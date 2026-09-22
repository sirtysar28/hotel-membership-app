<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Transaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $hotelScope = $user->hasGlobalAccess() ? null : $user->hotel_id;

        $memberQuery = Member::query()->when($hotelScope, fn ($q) => $q->where('hotel_id', $hotelScope));
        $txnQuery = Transaction::query()->when($hotelScope, fn ($q) => $q->where('hotel_id', $hotelScope));

        // KPI
        $totalMembers = (clone $memberQuery)->count();
        $paidMembers = (clone $memberQuery)->where('membership_type', 'paid')->count();
        $freeMembers = (clone $memberQuery)->where('membership_type', 'free')->count();
        $activeMembers = (clone $memberQuery)->active()->count();
        $membershipRevenue = (clone $memberQuery)->where('membership_type', 'paid')->active()->count()
            * (float) \App\Models\Setting::get('paid_membership_price', 2200000);
        $hotelRevenue = (clone $txnQuery)->whereIn('type', ['hotel_stay'])->sum('amount');
        $fnbRevenue = (clone $txnQuery)->whereIn('type', ['restaurant', 'bar', 'banquet', 'other_fnb'])->sum('amount');
        $totalRevenue = $membershipRevenue + $hotelRevenue + $fnbRevenue;

        // Member growth 6 bulan terakhir (paid vs free per bulan)
        $growth = collect(range(5, 0))->map(function ($i) use ($memberQuery) {
            $month = now()->subMonths($i);
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
        $revenueGrowth = collect(range(5, 0))->map(function ($i) use ($txnQuery) {
            $month = now()->subMonths($i);
            $sum = (clone $txnQuery)
                ->whereYear('transaction_date', $month->year)
                ->whereMonth('transaction_date', $month->month)
                ->sum('amount');

            return [
                'label' => $month->translatedFormat('M Y'),
                'sum' => (float) $sum,
            ];
        });

        // Distribution
        $levelDistribution = \App\Models\MembershipLevel::ordered()->get()
            ->mapWithKeys(fn ($level) => [$level->name => (clone $memberQuery)->where('level_id', $level->id)->count()]);

        $levelColors = \App\Models\MembershipLevel::ordered()->get()
            ->mapWithKeys(fn ($level) => [$level->name => $level->card_color ?: '#1e6bb8']);

        $hotelDistribution = \App\Models\Hotel::query()
            ->when($hotelScope, fn ($q) => $q->where('id', $hotelScope))
            ->get()
            ->mapWithKeys(fn ($hotel) => [$hotel->name => $hotel->members()->count()]);

        // Revenue per jenis transaksi
        $revenueByType = collect(Transaction::TYPES)
            ->mapWithKeys(fn ($label, $type) => [$label => (float) (clone $txnQuery)->where('type', $type)->sum('amount')])
            ->filter(fn ($sum) => $sum > 0);

        // Status distribution
        $statusDistribution = collect(['active', 'pending_payment', 'pending_review', 'inactive', 'expired'])
            ->mapWithKeys(fn ($status) => [$status => (clone $memberQuery)->where('status', $status)->count()]);

        $recentMembers = (clone $memberQuery)->with('hotel', 'level')->latest()->limit(8)->get();
        $pendingDuplicates = \App\Models\DuplicateCheck::where('status', 'potential_duplicate')->count();
        $pendingPayments = \App\Models\Payment::whereIn('status', ['pending', 'payment_submitted'])
            ->when($hotelScope, fn ($q) => $q->whereHas('member', fn ($q2) => $q2->where('hotel_id', $hotelScope)))
            ->count();

        return view('admin.dashboard', compact(
            'totalMembers', 'paidMembers', 'freeMembers', 'activeMembers', 'membershipRevenue', 'hotelRevenue', 'fnbRevenue',
            'totalRevenue', 'levelDistribution', 'levelColors', 'hotelDistribution', 'growth', 'revenueGrowth',
            'revenueByType', 'statusDistribution',
            'recentMembers', 'pendingDuplicates', 'pendingPayments',
        ));
    }
}
