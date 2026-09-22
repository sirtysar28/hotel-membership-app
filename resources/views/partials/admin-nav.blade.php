@php $role = auth()->user()->role; $canAdmin = in_array($role, ['super_admin','hotel_admin','membership_admin']); $canVoucher = in_array($role, ['super_admin','hotel_admin','membership_admin','manager']); $canApprove = in_array($role, ['super_admin','hotel_admin','manager']); @endphp
<a href="{{ route('admin.dashboard') }}" title="Dashboard" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white font-medium' : '' }}">
    <span class="nav-icon w-5 text-center shrink-0">▦</span> <span class="nav-label">Dashboard</span>
</a>

@if($canAdmin)
    <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wider text-brand-300 nav-section-title">Membership</div>
    <a href="{{ route('admin.members.index') }}" title="Members" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.members.*') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">👤</span> <span class="nav-label">Members</span>
    </a>
    <a href="{{ route('admin.duplicates.index') }}" title="Duplicate Review" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.duplicates.*') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">⧉</span> <span class="nav-label">Duplicate Review</span>
    </a>
    <a href="{{ route('admin.levels.index') }}" title="Levels & Rules" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.levels.*') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">★</span> <span class="nav-label">Levels &amp; Rules</span>
    </a>
    <a href="{{ route('admin.benefits.index') }}" title="Benefits" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.benefits.*') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">▤</span> <span class="nav-label">Benefits</span>
    </a>
@endif

@if($canVoucher)
    <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wider text-brand-300 nav-section-title">Voucer</div>
    <a href="{{ route('admin.vouchers.index') }}" title="Voucer Management" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.vouchers.*') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">🎟</span> <span class="nav-label">Voucer Management</span>
    </a>
@endif
@if($canApprove)
    @php
        $pendingCount = \App\Models\RedemptionRequest::where('status', \App\Models\RedemptionRequest::PENDING)
            ->when(auth()->user()->hotel_id && ! auth()->user()->hasGlobalAccess(),
                fn ($q) => $q->whereHas('member', fn ($m) => $m->where('hotel_id', auth()->user()->hotel_id)))
            ->count();
    @endphp
    <a href="{{ route('admin.redemptions') }}" title="Approval Penukaran" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.redemptions') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">✓</span>
        <span class="nav-label flex items-center gap-2">Approval Penukaran
            @if($pendingCount > 0)
                <span class="bg-amber-400 text-brand-900 text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center">{{ $pendingCount }}</span>
            @endif
        </span>
    </a>
@endif

@if(in_array($role, ['super_admin','hotel_admin','finance','membership_admin']))
    <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wider text-brand-300 nav-section-title">Finance</div>
    <a href="{{ route('admin.payments.index') }}" title="Payments" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.payments.*') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">₹</span> <span class="nav-label">Payments</span>
    </a>
@endif

<div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wider text-brand-300 nav-section-title">Monitoring</div>
<a href="{{ route('admin.reports.index', 'members') }}" title="Reports" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.reports.*') ? 'bg-white/10 text-white font-medium' : '' }}">
    <span class="nav-icon w-5 text-center shrink-0">▦▦</span> <span class="nav-label">Reports</span>
</a>
@if(in_array($role, ['super_admin','hotel_admin','management']))
    <a href="{{ route('admin.audit-logs') }}" title="Audit Logs" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.audit-logs') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">≡</span> <span class="nav-label">Audit Logs</span>
    </a>
@endif
@if(in_array($role, ['super_admin','hotel_admin','management','membership_admin']))
    <a href="{{ route('admin.email-logs') }}" title="Email Logs" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.email-logs') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">✉</span> <span class="nav-label">Email Logs</span>
    </a>
@endif

@if($role === 'super_admin')
    <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wider text-brand-300 nav-section-title">System</div>
    <a href="{{ route('admin.hotels.index') }}" title="Hotels" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.hotels.*') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">🏨</span> <span class="nav-label">Hotels</span>
    </a>
    <a href="{{ route('admin.users.index') }}" title="Users & Roles" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">👥</span> <span class="nav-label">Users &amp; Roles</span>
    </a>
    <a href="{{ route('admin.settings.index') }}" title="Settings" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('admin.settings.*') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">⚙</span> <span class="nav-label">Settings</span>
    </a>
@endif

@if(in_array($role, ['super_admin','hotel_admin','membership_admin','staff']))
    <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wider text-brand-300 nav-section-title">Front Office</div>
    <a href="{{ route('staff.index') }}" title="Staff / Redeem Visit" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('staff.index') || request()->routeIs('staff.members.*') || request()->routeIs('staff.search') || request()->routeIs('staff.scan') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">⌕</span> <span class="nav-label">Staff / Redeem Visit</span>
    </a>
    <a href="{{ route('staff.vouchers') }}" title="Penukaran Voucer" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/10 {{ request()->routeIs('staff.vouchers') || request()->routeIs('staff.vouchers.*') ? 'bg-white/10 text-white font-medium' : '' }}">
        <span class="nav-icon w-5 text-center shrink-0">🎟</span> <span class="nav-label">Penukaran Voucer</span>
    </a>
@endif
