<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelController extends Controller
{
    public function index(): View
    {
        return view('admin.hotels.index', ['hotels' => Hotel::orderBy('name')->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.hotels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:hotels,code'],
            'name' => ['required', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);

        $hotel = Hotel::create($validated);
        AuditLog::record('hotel_created', 'Hotel', $hotel->id, "Hotel dibuat: {$hotel->name}");

        return redirect()->route('admin.hotels.index')->with('success', 'Hotel berhasil ditambahkan.');
    }

    public function edit(Hotel $hotel): View
    {
        return view('admin.hotels.edit', ['hotel' => $hotel]);
    }

    public function update(Request $request, Hotel $hotel): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:hotels,code,' . $hotel->id],
            'name' => ['required', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $hotel->update($validated);
        AuditLog::record('hotel_updated', 'Hotel', $hotel->id, "Hotel diperbarui: {$hotel->name}");

        return redirect()->route('admin.hotels.index')->with('success', 'Hotel berhasil diperbarui.');
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        if ($hotel->members()->exists()) {
            return back()->with('error', 'Hotel memiliki member — tidak dapat dihapus.');
        }

        $hotel->delete();
        AuditLog::record('hotel_deleted', 'Hotel', null, "Hotel dihapus: {$hotel->name}");

        return redirect()->route('admin.hotels.index')->with('success', 'Hotel dihapus.');
    }
}
