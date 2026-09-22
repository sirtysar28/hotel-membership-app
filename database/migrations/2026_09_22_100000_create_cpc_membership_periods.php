<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CPC v2.0 §2, §3, §15 — Periode keanggotaan (khususnya Diamond berbayar).
 * Setiap pembayaran Diamond menghasilkan periode baru; voucher terikat ke periode.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->unsignedTinyInteger('period_no')->default(1);   // Periode #1, #2, ...
            $table->string('membership_type', 10);                  // paid | free
            $table->decimal('price', 15, 2)->default(0);            // Rp2.200.000 utk Diamond
            $table->date('start_date');                             // tanggal pembayaran berhasil
            $table->date('end_date');                               // start + masa berlaku (contoh: 21 Sep 2027)
            $table->string('status', 20)->default('active');        // active | expired | cancelled
            $table->string('payment_reference', 60)->nullable();    // no. invoice/referensi pembayaran
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_periods');
    }
};
