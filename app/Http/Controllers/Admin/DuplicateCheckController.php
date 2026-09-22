<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DuplicateCheck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DuplicateCheckController extends Controller
{
    public function index(Request $request): View
    {
        $duplicates = DuplicateCheck::with(['matchedMember.hotel', 'matchedMember.level', 'resolver'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.duplicates.index', [
            'duplicates' => $duplicates,
            'status' => $request->query('status'),
        ]);
    }

    public function resolve(Request $request, DuplicateCheck $duplicateCheck): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:confirmed,rejected,merged'],
            'resolution_note' => ['nullable', 'string', 'max:500'],
        ]);

        $duplicateCheck->update([
            'status' => $validated['decision'],
            'resolution_note' => $validated['resolution_note'] ?? null,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        // Jika dikonfirmasi sebagai duplikat yang valid, tandikan member lama agar jelas
        if ($validated['decision'] === 'confirmed' && $duplicateCheck->matchedMember) {
            AuditLog::record('duplicate_confirmed', 'Member', $duplicateCheck->matched_member_id,
                'Duplikat dikonfirmasi: pendaftaran baru ditolak');
        }

        return back()->with('success', 'Duplicate check diselesaikan.');
    }
}
