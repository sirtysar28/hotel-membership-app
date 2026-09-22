{{-- CPC v2.0 §13 — Slip cetak voucer (standalone, siap print / print-to-PDF browser) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Voucer {{ $redemption->voucher_no }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, Helvetica, sans-serif; background: #eef2f7; color: #1f2937; padding: 24px; }
        .slip { max-width: 420px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden;
                box-shadow: 0 4px 24px rgba(15, 42, 74, .12); }
        .slip-head { background: linear-gradient(135deg, #0f2a4a, #1e6bb8); color: #fff; padding: 20px 24px; text-align: center; }
        .slip-head h1 { font-size: 18px; letter-spacing: 1px; }
        .slip-head p { font-size: 11px; opacity: .85; margin-top: 4px; letter-spacing: 2px; }
        .badge { display: inline-block; margin-top: 10px; background: #d4af37; color: #0f2a4a; font-weight: 700;
                 font-size: 11px; padding: 4px 14px; border-radius: 999px; letter-spacing: 1px; }
        .body { padding: 20px 24px; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px dashed #e5e7eb; font-size: 12.5px; }
        .row:last-of-type { border-bottom: none; }
        .row .k { color: #6b7280; white-space: nowrap; }
        .row .v { font-weight: 600; text-align: right; word-break: break-all; }
        .voucher-id { text-align: center; margin: 14px 0 4px; }
        .voucher-id .no { font-family: 'Courier New', monospace; font-size: 22px; font-weight: 700; letter-spacing: 2px; color: #0f2a4a; }
        .voucher-id .label { font-size: 10px; color: #9ca3af; letter-spacing: 2px; text-transform: uppercase; }
        .type-box { text-align: center; margin: 10px 0 2px; }
        .type-box span { background: #eef6ff; border: 1px solid #bfd9f5; color: #1e6bb8; border-radius: 8px;
                         padding: 6px 16px; font-weight: 700; font-size: 13px; display: inline-block; }
        .sign { display: flex; justify-content: space-between; margin-top: 26px; padding: 0 24px 8px; font-size: 11px; color: #6b7280; }
        .sign div { text-align: center; width: 45%; }
        .sign .line { margin-top: 42px; border-top: 1px solid #9ca3af; padding-top: 4px; }
        .foot { background: #f9fafb; padding: 12px 24px; text-align: center; font-size: 10px; color: #9ca3af; }
        .actions { max-width: 420px; margin: 16px auto 0; display: flex; gap: 10px; justify-content: center; }
        .actions a, .actions button { font-size: 13px; font-weight: 600; padding: 9px 18px; border-radius: 8px; border: none; cursor: pointer; }
        .btn-print { background: #1e6bb8; color: #fff; }
        .btn-back { background: #e5e7eb; color: #374151; text-decoration: none; display: inline-block; }
        @media print {
            body { background: #fff; padding: 0; }
            .actions { display: none; }
            .slip { box-shadow: none; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="slip">
        <div class="slip-head">
            <h1>CIPUTRA PREMIERE CLUB</h1>
            <p>VOUCHER REDEMPTION SLIP</p>
            <span class="badge">APPROVED &amp; REDEEMED</span>
        </div>

        <div class="body">
            <div class="voucher-id">
                <div class="label">ID Voucer</div>
                <div class="no">{{ $redemption->voucher_no }}</div>
            </div>
            <div class="type-box">
                <span>{{ $redemption->voucher?->type?->name ?? $redemption->type?->name ?? '-' }}</span>
            </div>

            <div style="height:12px"></div>

            <div class="row"><span class="k">ID Anggota</span><span class="v">{{ $redemption->member->member_no }}</span></div>
            <div class="row"><span class="k">Nama Anggota</span><span class="v">{{ $redemption->member->full_name }}</span></div>
            <div class="row"><span class="k">Tingkat</span><span class="v">{{ $redemption->member->level?->name ?? '-' }}</span></div>
            <div class="row"><span class="k">Hotel / Unit</span><span class="v">{{ $redemption->hotel?->name ?? $redemption->member->hotel?->name ?? '-' }}</span></div>
            @if($redemption->outlet)
                <div class="row"><span class="k">Outlet</span><span class="v">{{ $redemption->outlet }}</span></div>
            @endif
            <div class="row"><span class="k">Diajukan Oleh (Staf)</span><span class="v">{{ $redemption->requester?->name ?? '-' }}<br><small style="font-weight:400">{{ $redemption->created_at->format('d M Y, H:i') }}</small></span></div>
            <div class="row"><span class="k">Disetujui Oleh (Manajer)</span><span class="v">{{ $redemption->decider?->name ?? '-' }}<br><small style="font-weight:400">{{ $redemption->decided_at?->format('d M Y, H:i') ?? '-' }}</small></span></div>
            <div class="row"><span class="k">Ditukarkan</span><span class="v">{{ $redemption->voucher?->redeemed_at?->format('d M Y, H:i') ?? $redemption->decided_at?->format('d M Y, H:i') ?? '-' }}</span></div>
            <div class="row"><span class="k">Status</span><span class="v">{{ $redemption->status }} · Voucer {{ $redemption->voucher?->status ?? '' }}</span></div>

            <div class="sign">
                <div>Diambil oleh,<div class="line">.............................</div></div>
                <div>Manajer,<div class="line">{{ $redemption->decider?->name ?? '.............................' }}</div></div>
            </div>
        </div>

        <div class="foot">
            Slip ini sah setelah persetujuan manajer — CPC §13.<br>
            Dicetak: {{ now()->translatedFormat('d M Y, H:i') }} · oleh {{ auth()->user()->name }}
        </div>
    </div>

    <div class="actions">
        <button class="btn-print" onclick="window.print()">🖨 Cetak / Simpan PDF</button>
        <a class="btn-back" href="{{ route('admin.redemptions') }}">← Kembali</a>
    </div>
</body>
</html>
