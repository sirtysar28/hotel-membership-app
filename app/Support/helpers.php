<?php

if (! function_exists('format_idr')) {
    function format_idr($amount, bool $withPrefix = true): string
    {
        $formatted = number_format((float) $amount, 0, ',', '.');

        return $withPrefix ? 'Rp' . $formatted : $formatted;
    }
}

if (! function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        $map = [
            'active' => 'bg-emerald-100 text-emerald-800',
            'pending_payment' => 'bg-amber-100 text-amber-800',
            'pending_review' => 'bg-orange-100 text-orange-800',
            'inactive' => 'bg-gray-100 text-gray-600',
            'expired' => 'bg-red-100 text-red-700',
            'paid' => 'bg-emerald-100 text-emerald-800',
            'pending' => 'bg-amber-100 text-amber-800',
            'payment_submitted' => 'bg-blue-100 text-blue-800',
            'failed' => 'bg-red-100 text-red-700',
            'refunded' => 'bg-purple-100 text-purple-800',
            'cancelled' => 'bg-gray-100 text-gray-600',
            'potential_duplicate' => 'bg-orange-100 text-orange-800',
            'confirmed' => 'bg-emerald-100 text-emerald-800',
            'rejected' => 'bg-gray-100 text-gray-600',
            'merged' => 'bg-blue-100 text-blue-800',
            'unpaid' => 'bg-amber-100 text-amber-800',
            // Voucher statuses (CPC v2.0)
            'AVAILABLE' => 'bg-emerald-100 text-emerald-700',
            'PENDING_APPROVAL' => 'bg-amber-100 text-amber-800',
            'REDEEMED' => 'bg-blue-100 text-blue-800',
            'EXPIRED' => 'bg-gray-200 text-gray-600',
            'CANCELLED' => 'bg-red-100 text-red-700',
            'PENDING' => 'bg-amber-100 text-amber-800',
            'APPROVED' => 'bg-emerald-100 text-emerald-800',
        ];

        $labels = [
            'AVAILABLE' => 'Available',
            'PENDING_APPROVAL' => 'Pending Approval',
            'REDEEMED' => 'Redeemed',
            'EXPIRED' => 'Expired',
            'CANCELLED' => 'Cancelled',
            'PENDING' => 'Pending Approval',
            'APPROVED' => 'Approved',
            'REJECTED' => 'Rejected',
        ];

        $class = $map[$status] ?? 'bg-gray-100 text-gray-600';
        $label = $labels[$status] ?? ucwords(str_replace('_', ' ', $status));

        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $class . '">' . $label . '</span>';
    }
}

if (! function_exists('gender_label')) {
    function gender_label(?string $gender): string
    {
        return match ($gender) {
            'male' => 'Male',
            'female' => 'Female',
            default => '-',
        };
    }
}
