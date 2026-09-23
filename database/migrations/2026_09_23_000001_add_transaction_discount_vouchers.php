<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Voucer diskon otomatis dari transaksi (F&B / resto / bar / banquet / room):
 * - vouchers.transaction_id   : transaksi pemicu voucer
 * - vouchers.discount_percent : persentase diskon dari benefit level (mis. 5.00)
 * - vouchers.discount_amount  : nilai rupiah diskon (amount transaksi × %)
 * - voucher_types fnb_discount & room_discount : jenis voucer hasil transaksi
 *
 * Alur: staff input transaksi → sistem generate voucer diskon sesuai benefit
 * level member & jenis transaksi → status PENDING_APPROVAL → manajer approve/reject.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('transaction_id')->nullable()->after('membership_period_id')
                ->constrained('transactions')->nullOnDelete();
            $table->decimal('discount_percent', 5, 2)->nullable()->after('source');
            $table->decimal('discount_amount', 15, 2)->nullable()->after('discount_percent');
        });

        // Jenis voucer hasil transaksi (idempotent)
        \App\Models\VoucherType::updateOrCreate(
            ['code' => 'fnb_discount'],
            ['name' => 'Voucer Diskon F&B', 'description' => 'Diskon otomatis dari transaksi restaurant / bar / banquet / F&B lain sesuai benefit level member', 'sort_order' => 7, 'is_active' => true],
        );
        \App\Models\VoucherType::updateOrCreate(
            ['code' => 'room_discount'],
            ['name' => 'Voucer Diskon Kamar', 'description' => 'Diskon otomatis dari transaksi hotel stay sesuai benefit level member', 'sort_order' => 8, 'is_active' => true],
        );
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transaction_id');
            $table->dropColumn(['discount_percent', 'discount_amount']);
        });
    }
};
