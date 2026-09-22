<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Hotel;
use App\Models\Member;
use App\Models\Transaction;
use App\Services\EmailService;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(private MembershipService $membership) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $hotelScope = $user->hasGlobalAccess() ? null : $user->hotel_id;

        $query = Member::query()->with('hotel', 'level')
            ->when($hotelScope, fn ($q) => $q->where('hotel_id', $hotelScope));

        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w
                ->where('member_no', 'like', "%{$q}%")
                ->orWhere('full_name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%"));
        }
        if ($hotel = $request->query('hotel_id')) {
            $query->where('hotel_id', $hotel);
        }
        if ($type = $request->query('type')) {
            $query->where('membership_type', $type);
        }
        if ($level = $request->query('level_id')) {
            $query->where('level_id', $level);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $members = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('admin.members.index', [
            'members' => $members,
            'hotels' => Hotel::orderBy('name')->get(),
            'levels' => \App\Models\MembershipLevel::ordered()->get(),
            'filters' => $request->only(['q', 'hotel_id', 'type', 'level_id', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.members.create', [
            'hotels' => Hotel::where('is_active', true)->get(),
            'levels' => \App\Models\MembershipLevel::ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'phone_country_code' => ['nullable', 'string', 'max:8', 'regex:/^\+[0-9]{1,4}$/'],
            'proof_reference' => ['nullable', 'string', 'max:80'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:500'],
            'id_type' => ['nullable', 'in:ktp,passport,sim,other'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'hotel_id' => ['required', 'exists:hotels,id'],
            'membership_type' => ['required', 'in:paid,free'],
        ]);

        $member = $this->membership->registerMember($validated);

        return redirect()->route('admin.members.show', $member)
            ->with('success', "Member {$member->member_no} berhasil dibuat.");
    }

    public function show(Member $member): View
    {
        $member->load([
            'hotel', 'level', 'payments.verifier', 'activeCard', 'visits' => fn ($q) => $q->latest('visit_date')->limit(10),
            'transactions' => fn ($q) => $q->latest('transaction_date')->limit(10),
            'benefits.benefit',
            'vouchers.type', 'periods',
        ]);

        return view('admin.members.show', [
            'member' => $member,
            'engine' => app(\App\Services\LevelEngine::class),
            'voucherCounts' => $member->vouchers->groupBy('status')->map->count(),
        ]);
    }

    public function edit(Member $member): View
    {
        return view('admin.members.edit', [
            'member' => $member,
            'hotels' => Hotel::where('is_active', true)->get(),
            'levels' => \App\Models\MembershipLevel::ordered()->get(),
        ]);
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'phone_country_code' => ['nullable', 'string', 'max:8', 'regex:/^\+[0-9]{1,4}$/'],
            'proof_reference' => ['nullable', 'string', 'max:80'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:500'],
            'id_type' => ['nullable', 'in:ktp,passport,sim,other'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'hotel_id' => ['required', 'exists:hotels,id'],
            'level_id' => ['required', 'exists:membership_levels,id'],
            'valid_until' => ['nullable', 'date'],
        ]);

        $changes = array_filter($validated, fn ($v, $k) => $member->{$k} != $v, ARRAY_FILTER_USE_BOTH);
        $member->update($validated);

        AuditLog::record('member_updated', 'Member', $member->id, 'Update data member', $changes);

        return redirect()->route('admin.members.show', $member)->with('success', 'Data member diperbarui.');
    }

    public function updateStatus(Request $request, Member $member): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:pending_payment,pending_review,active,inactive,expired']]);

        $member->update(['status' => $validated['status']]);

        AuditLog::record('member_status_changed', 'Member', $member->id,
            "Status: {$member->status} -> {$validated['status']}");

        return back()->with('success', 'Status member diperbarui.');
    }

    public function card(Member $member): View
    {
        return view('admin.members.card', ['member' => $member]);
    }

    public function reissueCard(Member $member): RedirectResponse
    {
        $this->membership->issueCard($member, 'Re-issue oleh admin');

        return back()->with('success', 'Kartu digital baru berhasil diterbitkan.');
    }

    public function sendRenewal(Member $member): RedirectResponse
    {
        app(EmailService::class)->sendRenewalReminder($member);

        return back()->with('success', 'Email renewal reminder terkirim.');
    }
}
