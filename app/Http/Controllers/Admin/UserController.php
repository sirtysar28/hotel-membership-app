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

        $user = User::create($validated);
        AuditLog::record('user_created', 'User', $user->id, "User dibuat: {$user->email} ({$user->role})");

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dibuat.');
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
