<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard + filter (update #3/#7/#14):
     * filter unit (Jakarta/Semarang), tanggal (from/to) dan preset periode
     * (hari ini / 7 hari / bulan ini / pilih bulan).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $hotelScope = $user->hasGlobalAccess() ? null : $user->hotel_id;

        // ---------- Filter ----------
        $filters = $this->resolveFilters($request);
        $hotelId = $filters['hotel_id'];
        $from = $filters['from'];
        $to = $filters['to'];

        $memberQuery = Member::query()
            ->when($hotelScope, fn ($q) => $q->where('hotel_id', $hotelScope))
            ->when($hotelId, fn ($q) => $q->where('hotel_id', $hotelId));

        // Transaksi & member baru mengikuti rentang tanggal filter
        $txnQuery = Transaction::query()
            ->when($hotelScope, fn ($q) => $q->where('hotel_id', $hotelScope))
            ->when($hotelId, fn ($q) => $q->where('hotel_id', $hotelId))
            ->when($from, fn ($q) => $q->whereDate('transaction_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('transaction_date', '<=', $to));

        $rangeMemberQuery = (clone $memberQuery)
            ->when($from, fn ($q) => $q->whereDate('joined_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('joined_at', '<=', $to));

        // ---------- KPI ----------
        $totalMembers = (clone $memberQuery)->count();
        $paidMembers = (clone $memberQuery)->where('membership_type', 'paid')->count();
        $freeMembers = (clone $memberQuery)->where('membership_type', 'free')->count();
        $activeMembers = (clone $memberQuery)->active()->count();
        $newMembers = (clone $rangeMemberQuery)->count(); // member baru pada rentang filter

        $membershipRevenue = (clone $rangeMemberQuery)->where('membership_type', 'paid')->count()
            * (float) \App\Models\Setting::get('paid_membership_price', 2200000);
        $hotelRevenue = (clone $txnQuery)->whereIn('type', ['hotel_stay'])->sum('amount');
        $fnbRevenue = (clone $txnQuery)->whereIn('type', ['restaurant', 'bar', 'banquet', 'other_fnb'])->sum('amount');
        $totalRevenue = $membershipRevenue + $hotelRevenue + $fnbRevenue;

        // ---------- Anchor chart: 6 bulan berakhir pada bulan akhir filter ----------
        $anchor = $to ? \Illuminate\Support\Carbon::parse($to) : now();

        // Member growth 6 bulan terakhir (paid vs free per bulan)
        $growth = collect(range(5, 0))->map(function ($i) use ($memberQuery, $anchor) {
            $month = $anchor->copy()->subMonths($i);
            $base = (clone $memberQuery)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month);

            return [
                'label' => $month->translatedFormat('M Y'),
                'count' => (clone $base)->count(),
                'paid' => (clone $base)->where('membership_type', 'paid')->count(),
                'free' => (clone $base)->where('membership_type', 'free')->count(),
            ];
        });

        // Revenue per bulan (6 bulan)
        $revenueGrowth = collect(range(5, 0))->map(function ($i) use ($txnQuery, $anchor) {
            $month = $anchor->copy()->subMonths($i);
            $sum = (clone $txnQuery)
                ->whereYear('transaction_date', $month->year)
                ->whereMonth('transaction_date', $month->month)
                ->sum('amount');

            return [
                'label' => $month->translatedFormat('M Y'),
                'sum' => (float) $sum,
            ];
        });

        // ---------- Distribution ----------
        $levelDistribution = \App\Models\MembershipLevel::ordered()->get()
            ->mapWithKeys(fn ($level) => [$level->name => (clone $memberQuery)->where('level_id', $level->id)->count()]);

        $levelColors = \App\Models\MembershipLevel::ordered()->get()
            ->mapWithKeys(fn ($level) => [$level->name => $level->card_color ?: '#1e6bb8']);

        $hotelDistribution = Hotel::query()
            ->when($hotelScope, fn ($q) => $q->where('id', $hotelScope))
            ->when($hotelId, fn ($q) => $q->where('id', $hotelId))
            ->get()
            ->mapWithKeys(fn ($hotel) => [$hotel->name => $hotel->members()->count()]);

        // Revenue per jenis transaksi
        $revenueByType = collect(Transaction::TYPES)
            ->mapWithKeys(fn ($label, $type) => [$label => (float) (clone $txnQuery)->where('type', $type)->sum('amount')])
            ->filter(fn ($sum) => $sum > 0);

        // Status distribution
        $statusDistribution = collect(['active', 'pending_payment', 'pending_review', 'inactive', 'expired'])
            ->mapWithKeys(fn ($status) => [$status => (clone $memberQuery)->where('status', $status)->count()]);

        $recentMembers = (clone $rangeMemberQuery)->with('hotel', 'level')->latest()->limit(8)->get();
        $pendingDuplicates = \App\Models\DuplicateCheck::where('status', 'potential_duplicate')->count();
        $pendingPayments = \App\Models\Payment::whereIn('status', ['pending', 'payment_submitted'])
            ->when($hotelScope || $hotelId, fn ($q) => $q->whereHas('member', fn ($q2) => $q2
                ->when($hotelScope, fn ($q3) => $q3->where('hotel_id', $hotelScope))
                ->when($hotelId, fn ($q3) => $q3->where('hotel_id', $hotelId))))
            ->count();

        $hotels = Hotel::where('is_active', true)->orderBy('name')->get();
        $filterActive = (bool) ($hotelId || $from || $to);

        return view('admin.dashboard', compact(
            'totalMembers', 'paidMembers', 'freeMembers', 'activeMembers', 'newMembers',
            'membershipRevenue', 'hotelRevenue', 'fnbRevenue', 'totalRevenue',
            'levelDistribution', 'levelColors', 'hotelDistribution', 'growth', 'revenueGrowth',
            'revenueByType', 'statusDistribution',
            'recentMembers', 'pendingDuplicates', 'pendingPayments',
            'filters', 'hotels', 'filterActive',
        ));
    }

    /**
     * Normalisasi filter dashboard: preset (hari_ini / 7_hari / bulan_ini / bulan /
     * rentang) + hotel_id → from/to.
     */
    private function resolveFilters(Request $request): array
    {
        $hotelId = $request->filled('hotel_id') ? (int) $request->query('hotel_id') : null;
        $preset = $request->query('preset', 'semua');
        $from = $request->query('from');
        $to = $request->query('to');
        $month = $request->query('month'); // YYYY-MM

        switch ($preset) {
            case 'hari_ini':
                $from = $to = today()->toDateString();
                break;
            case '7_hari':
                $from = today()->subDays(6)->toDateString();
                $to = today()->toDateString();
                break;
            case 'bulan_ini':
                $from = today()->startOfMonth()->toDateString();
                $to = today()->endOfMonth()->toDateString();
                break;
            case 'bulan':
                if (preg_match('/^\d{4}-\d{2}$/', (string) $month)) {
                    $m = \Illuminate\Support\Carbon::parse($month . '-01');
                    $from = $m->startOfMonth()->toDateString();
                    $to = $m->endOfMonth()->toDateString();
                }
                break;
            case 'rentang':
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $from)) {
                    $from = null;
                }
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $to)) {
                    $to = null;
                }
                break;
            default:
                $preset = 'semua';
                $from = $to = null;
        }

        return [
            'hotel_id' => $hotelId,
            'preset' => $preset,
            'month' => $month,
            'from' => $from,
            'to' => $to,
        ];
    }
}
