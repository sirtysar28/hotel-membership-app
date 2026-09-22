<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Hotel;
use App\Models\MembershipBenefit;
use App\Models\MembershipLevel;
use App\Models\MemberBenefit;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BenefitController extends Controller
{
    public const CATEGORIES = [
        'room' => 'Benefit Kamar',
        'fnb' => 'Benefit F&B',
        'discount' => 'Discount',
        'voucher' => 'Voucher',
        'complimentary' => 'Complimentary',
        'other' => 'Lainnya',
    ];

    public function index(): View
    {
        $benefits = MembershipBenefit::with(['level', 'hotel'])->orderBy('name')->paginate(15);

        return view('admin.benefits.index', ['benefits' => $benefits]);
    }

    public function create(): View
    {
        return view('admin.benefits.create', [
            'levels' => MembershipLevel::ordered()->get(),
            'hotels' => Hotel::orderBy('name')->get(),
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'in:' . implode(',', array_keys(self::CATEGORIES))],
            'level_id' => ['nullable', 'exists:membership_levels,id'],
            'hotel_id' => ['nullable', 'exists:hotels,id'],
            'value' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $benefit = MembershipBenefit::create($validated);
        AuditLog::record('benefit_created', 'MembershipBenefit', $benefit->id, "Benefit dibuat: {$benefit->name}");

        return redirect()->route('admin.benefits.index')->with('success', 'Benefit berhasil dibuat.');
    }

    public function edit(MembershipBenefit $benefit): View
    {
        return view('admin.benefits.edit', [
            'benefit' => $benefit,
            'levels' => MembershipLevel::ordered()->get(),
            'hotels' => Hotel::orderBy('name')->get(),
            'categories' => self::CATEGORIES,
        ]);
    }

    public function update(Request $request, MembershipBenefit $benefit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'in:' . implode(',', array_keys(self::CATEGORIES))],
            'level_id' => ['nullable', 'exists:membership_levels,id'],
            'hotel_id' => ['nullable', 'exists:hotels,id'],
            'value' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['level_id'] = $validated['level_id'] ?: null;
        $validated['hotel_id'] = $validated['hotel_id'] ?: null;
        $validated['is_active'] = $request->boolean('is_active');

        $benefit->update($validated);
        AuditLog::record('benefit_updated', 'MembershipBenefit', $benefit->id, "Benefit diperbarui: {$benefit->name}");

        return redirect()->route('admin.benefits.index')->with('success', 'Benefit berhasil diperbarui.');
    }

    /** Berikan benefit (mis. voucher) ke member tertentu */
    public function grant(Request $request, MembershipBenefit $benefit): RedirectResponse
    {
        $validated = $request->validate([
            'member_no' => ['required', 'exists:members,member_no'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $member = Member::where('member_no', $validated['member_no'])->firstOrFail();

        MemberBenefit::create([
            'member_id' => $member->id,
            'benefit_id' => $benefit->id,
            'status' => 'active',
            'granted_at' => now(),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        AuditLog::record('benefit_granted', 'MemberBenefit', $member->id,
            "Benefit '{$benefit->name}' diberikan ke {$member->member_no}");

        return back()->with('success', "Benefit '{$benefit->name}' diberikan ke {$member->member_no}.");
    }
}
