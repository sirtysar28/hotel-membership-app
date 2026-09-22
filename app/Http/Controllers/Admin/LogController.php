<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function audit(Request $request): View
    {
        $logs = AuditLog::with('user')
            ->when($request->query('q'), fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('action', 'like', "%{$v}%")
                ->orWhere('description', 'like', "%{$v}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.logs.audit', ['logs' => $logs]);
    }

    public function email(Request $request): View
    {
        $logs = EmailLog::with('member')
            ->when($request->query('type'), fn ($q, $v) => $q->where('type', $v))
            ->when($request->query('q'), fn ($q, $v) => $q->where('to_email', 'like', "%{$v}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.logs.email', ['logs' => $logs, 'emailTypes' => [
            'welcome' => 'Welcome', 'upgrade' => 'Upgrade', 'renewal' => 'Renewal',
            'expiration' => 'Expiration', 'transaction' => 'Transaction', 'payment' => 'Payment',
        ]]);
    }
}
