<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Member;
use App\Models\MembershipLevel;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $levels = MembershipLevel::ordered()->with('activeRule')->get();

        return view('home', [
            'hotels' => Hotel::where('is_active', true)->get(),
            'levels' => $levels,
            'price' => Setting::get('paid_membership_price', 2200000),
            'totalMembers' => Member::count(),
            'activeMembers' => Member::active()->count(),
            'totalVisits' => (int) Member::sum('total_visits'),
        ]);
    }
}
