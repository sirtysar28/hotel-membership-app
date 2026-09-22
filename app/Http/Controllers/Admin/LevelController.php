<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MembershipLevel;
use App\Models\MembershipRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Rule Engine configuration: admin mengubah threshold level tanpa ubah kode.
 */
class LevelController extends Controller
{
    public function index(): View
    {
        $levels = MembershipLevel::ordered()->with(['rules' => fn ($q) => $q->latest()])->get();

        return view('admin.levels.index', ['levels' => $levels]);
    }

    public function updateRule(Request $request, MembershipRule $rule): RedirectResponse
    {
        $validated = $request->validate([
            'min_visit' => ['required', 'integer', 'min:0'],
            'max_visit' => ['nullable', 'integer', 'min:0'],
            'min_spending' => ['required', 'numeric', 'min:0'],
            'evaluation_period_months' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $rule->only(['min_visit', 'max_visit', 'min_spending', 'evaluation_period_months', 'is_active']);

        $validated['max_visit'] = $validated['max_visit'] ?? null;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['min_spending'] = (float) $validated['min_spending'];

        $rule->update($validated);

        AuditLog::record('rule_updated', 'MembershipRule', $rule->id,
            "Rule level {$rule->level->name} diperbarui", ['before' => $before, 'after' => $validated]);

        return back()->with('success', 'Rule ' . $rule->level->name . ' berhasil diperbarui.');
    }
}
