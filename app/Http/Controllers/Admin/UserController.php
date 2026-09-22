<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('hotel')->where('role', '!=', 'member')->orderBy('name')->paginate(15);

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => collect(User::ROLES)->except('member'),
            'hotels' => Hotel::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'hotel_id' => ['nullable', 'exists:hotels,id'],
        ]);

        // Update #18 — akun staf & manajer BUTUH APPROVAL sebelum dapat login;
        // dibuat oleh super admin lalu disetujui super admin/manajer berwenang.
        $needsApproval = in_array($validated['role'], User::APPROVAL_REQUIRED_ROLES, true);
        $validated['approval_status'] = $needsApproval ? 'pending' : 'approved';

        $user = User::create($validated);

        AuditLog::record('user_created', 'User', $user->id,
            "User dibuat: {$user->email} ({$user->role})" . ($needsApproval ? ' — menunggu approval' : ''));

        return redirect()->route('admin.users.index')
            ->with('success', $needsApproval
                ? 'User berhasil dibuat dan MENUNGGU APPROVAL — login diblokir sampai disetujui.'
                : 'User berhasil dibuat.');
    }

    /** Update #18 — setujui akun staff/manager yang menunggu approval. */
    public function approve(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Tidak dapat menyetujui akun sendiri.');
        }

        if ($user->approval_status !== 'pending') {
            return back()->with('error', 'Akun ini tidak berstatus menunggu approval.');
        }

        $user->update([
            'approval_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        AuditLog::record('user_approved', 'User', $user->id,
            "Akun {$user->email} ({$user->role}) DISETUJUI oleh " . $request->user()->email);
        app(\App\Services\EmailService::class)->sendAccountApproved($user, $request->user());

        return back()->with('success', "Akun {$user->email} disetujui — kini dapat login.");
    }

    /** Update #18 — tolak akun yang menunggu approval (login tetap diblokir). */
    public function reject(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:300'],
        ]);

        if ($user->approval_status !== 'pending') {
            return back()->with('error', 'Akun ini tidak berstatus menunggu approval.');
        }

        $user->update([
            'approval_status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'is_active' => false,
        ]);

        AuditLog::record('user_rejected', 'User', $user->id,
            "Akun {$user->email} ({$user->role}) DITOLAK oleh " . $request->user()->email
            . '. Alasan: ' . ($validated['reason'] ?? '-'));

        return back()->with('success', "Akun {$user->email} ditolak — login diblokir.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => collect(User::ROLES)->except('member'),
            'hotels' => Hotel::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'min:8'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'hotel_id' => ['nullable', 'exists:hotels,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);
        AuditLog::record('user_updated', 'User', $user->id, "User diperbarui: {$user->email}");

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();
        AuditLog::record('user_deleted', 'User', null, "User dihapus: {$user->email}");

        return redirect()->route('admin.users.index')->with('success', 'User dihapus.');
    }
}
