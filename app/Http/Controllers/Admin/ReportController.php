<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DuplicateCheck;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, string $type): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        if (! in_array($type, ['members', 'revenue', 'visits', 'duplicates'])) {
            abort(404);
        }

        $data = $this->buildReport($type, $request);
        $filters = $this->filters($request);

        return view('admin.reports.index', array_merge($data, [
            'type' => $type,
            'filters' => $filters,
            'hotels' => \App\Models\Hotel::orderBy('name')->get(),
            'levels' => \App\Models\MembershipLevel::ordered()->get(),
        ]));
    }

    public function export(Request $request, string $type): \Symfony\Component\HttpFoundation\Response
    {
        $data = $this->buildReport($type, $request);
        $rows = $data['rows'];
        $format = $request->query('format', 'csv');
        $filename = 'report-' . $type . '-' . now()->format('Ymd-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf', [
                'title' => $data['title'],
                'headers' => $data['headers'],
                'rows' => $rows,
                'generated' => now()->format('d M Y H:i'),
            ]);

            return $pdf->download($filename . '.pdf');
        }

        // CSV
        $callback = function () use ($rows, $data) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM utk Excel
            fputcsv($out, $data['headers']);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildReport(string $type, Request $request): array
    {
        $user = auth()->user();
        $hotelScope = $user->hasGlobalAccess() ? null : $user->hotel_id;
        $filters = $this->filters($request);

        return match ($type) {
            'members' => $this->memberReport($hotelScope, $filters),
            'revenue' => $this->revenueReport($hotelScope, $filters),
            'visits' => $this->visitReport($hotelScope, $filters),
            'duplicates' => $this->duplicateReport($filters),
        };
    }

    private function memberReport(?int $hotelScope, array $f): array
    {
        $query = Member::query()->with('hotel', 'level')
            ->when($hotelScope, fn ($q) => $q->where('hotel_id', $hotelScope))
            ->when($f['hotel_id'] ?? null, fn ($q, $v) => $q->where('hotel_id', $v))
            ->when($f['type'] ?? null, fn ($q, $v) => $q->where('membership_type', $v))
            ->when($f['level_id'] ?? null, fn ($q, $v) => $q->where('level_id', $v))
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($f['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($f['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderBy('member_no');

        $members = $query->get();

        $rows = $members->map(fn ($m) => [
            $m->member_no,
            $m->full_name,
            $m->email,
            $m->phone,
            $m->hotel->name,
            ucfirst($m->membership_type),
            $m->level->name,
            $m->created_at->format('d/m/Y'),
            ucfirst($m->status),
            $m->total_visits,
            number_format((float) $m->total_spending, 0, ',', '.'),
        ])->all();

        return [
            'title' => 'Member Report',
            'headers' => ['Member ID', 'Name', 'Email', 'Phone', 'Hotel', 'Type', 'Level', 'Registration Date', 'Status', 'Total Visit', 'Total Spending'],
            'rows' => $rows,
            'members' => $members,
        ];
    }

    private function revenueReport(?int $hotelScope, array $f): array
    {
        $price = (float) Setting::get('paid_membership_price', 2200000);

        $paidCount = Member::query()
            ->when($hotelScope, fn ($q) => $q->where('hotel_id', $hotelScope))
            ->when($f['hotel_id'] ?? null, fn ($q, $v) => $q->where('hotel_id', $v))
            ->where('membership_type', 'paid')->active()->count();

        $txns = Transaction::query()
            ->when($hotelScope, fn ($q) => $q->where('hotel_id', $hotelScope))
            ->when($f['hotel_id'] ?? null, fn ($q, $v) => $q->where('hotel_id', $v))
            ->when($f['from'] ?? null, fn ($q, $v) => $q->whereDate('transaction_date', '>=', $v))
            ->when($f['to'] ?? null, fn ($q, $v) => $q->whereDate('transaction_date', '<=', $v))
            ->get();

        $hotelRevenue = (float) $txns->where('type', 'hotel_stay')->sum('amount');
        $fnbRevenue = (float) $txns->whereIn('type', ['restaurant', 'bar', 'banquet', 'other_fnb'])->sum('amount');
        $otherRevenue = (float) $txns->where('type', 'other')->sum('amount');
        $membershipRevenue = $paidCount * $price;

        $summary = collect([
            ['Paid Membership Revenue', $membershipRevenue],
            ['Hotel Revenue', $hotelRevenue],
            ['F&B Revenue', $fnbRevenue],
            ['Other Revenue', $otherRevenue],
            ['TOTAL', $membershipRevenue + $hotelRevenue + $fnbRevenue + $otherRevenue],
        ]);

        $rows = $summary->map(fn ($s) => [$s[0], 'Rp ' . number_format($s[1], 0, ',', '.')])->all();

        return [
            'title' => 'Revenue Report',
            'headers' => ['Category', 'Amount'],
            'rows' => $rows,
            'summary' => $summary,
        ];
    }

    private function visitReport(?int $hotelScope, array $f): array
    {
        $visits = \App\Models\Visit::query()->with(['member', 'hotel', 'transaction', 'staff'])
            ->when($hotelScope, fn ($q) => $q->where('hotel_id', $hotelScope))
            ->when($f['hotel_id'] ?? null, fn ($q, $v) => $q->where('hotel_id', $v))
            ->when($f['from'] ?? null, fn ($q, $v) => $q->whereDate('visit_date', '>=', $v))
            ->when($f['to'] ?? null, fn ($q, $v) => $q->whereDate('visit_date', '<=', $v))
            ->orderByDesc('visit_date')
            ->get();

        $rows = $visits->map(fn ($v) => [
            $v->visit_date->format('d/m/Y'),
            $v->member->member_no,
            $v->member->full_name,
            $v->hotel->name,
            $v->transaction?->typeLabel() ?? '-',
            $v->transaction?->transaction_no ?? '-',
            'Rp ' . number_format((float) ($v->transaction?->amount ?? 0), 0, ',', '.'),
            $v->staff?->name ?? '-',
        ])->all();

        return [
            'title' => 'Visit Report',
            'headers' => ['Date', 'Member ID', 'Member', 'Hotel', 'Transaction', 'Transaction No', 'Amount', 'Staff'],
            'rows' => $rows,
            'visits' => $visits,
        ];
    }

    private function duplicateReport(array $f): array
    {
        $dups = DuplicateCheck::with(['matchedMember.hotel', 'resolver'])
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->latest()->get();

        $rows = $dups->map(fn ($d) => [
            $d->created_at->format('d/m/Y H:i'),
            $d->submitted_data['full_name'] ?? '-',
            $d->submitted_data['email'] ?? '-',
            $d->matchedMember?->member_no ?? '-',
            $this->formatMatched($d->matched_fields),
            ucfirst(str_replace('_', ' ', $d->status)),
            $d->resolver?->name ?? '-',
        ])->all();

        return [
            'title' => 'Duplicate Report',
            'headers' => ['Date Detected', 'Submitted Name', 'Submitted Email', 'Existing Member', 'Duplicate Field', 'Resolution', 'Resolved By'],
            'rows' => $rows,
            'duplicates' => $dups,
        ];
    }

    private function formatMatched(?array $fields): string
    {
        if (! $fields) {
            return '-';
        }

        $labels = ['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'id_number' => 'ID Number', 'dob' => 'DOB'];

        return collect($fields)
            ->filter(fn ($v) => $v !== 'different')
            ->map(fn ($v, $k) => ($labels[$k] ?? $k) . ': ' . ucfirst($v))
            ->implode('; ') ?: '-';
    }

    private function filters(Request $request): array
    {
        return $request->only(['hotel_id', 'type', 'level_id', 'status', 'from', 'to']);
    }
}
