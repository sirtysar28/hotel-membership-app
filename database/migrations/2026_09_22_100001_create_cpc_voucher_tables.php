<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CPC v2.0 §4, §5, §6, §10, §11 — Sistem Voucer:
 * - voucher_types  : data master jenis voucer (Sarapan, CL, Gym, dst) — configurable
 * - voucher_packages + items : komposisi paket (mis. 12 voucer) — configurable
 * - vouchers       : instans voucer individu dengan ID unik (VCH-260001-0001)
 * - redemption_requests : alur staff -> PENDING_APPROVAL -> manajer setujui/tolak (riwayat lengkap)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Master jenis voucer
        Schema::create('voucher_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();          // breakfast, cl, gym, room_upgrade, swimming_pool, room
            $table->string('name');                        // Voucer Sarapan, dst.
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Paket voucer (komposisi) — qty per jenis, tanpa hard-code
        Schema::create('voucher_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // "Paket Diamond 12 Voucer"
            $table->string('applies_to', 20)->default('paid'); // paid | free | both
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('voucher_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_package_id')->constrained('voucher_packages')->cascadeOnDelete();
            $table->foreignId('voucher_type_id')->constrained('voucher_types')->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(0);
            $table->timestamps();

            $table->unique(['voucher_package_id', 'voucher_type_id'], 'vpi_package_type_unique');
        });

        // Instans voucer individu
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 30)->unique();    // VCH-260001-0001 (ID Voucer unik)
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('voucher_type_id')->constrained('voucher_types');
            $table->foreignId('voucher_package_id')->nullable()->constrained('voucher_packages')->nullOnDelete();
            $table->foreignId('membership_period_id')->nullable()->constrained('membership_periods')->nullOnDelete();
            $table->string('source', 30)->default('registration'); // registration | diamond_payment | renewal | bonus
            $table->string('status', 20)->default('AVAILABLE');    // AVAILABLE | PENDING_APPROVAL | REDEEMED | EXPIRED | CANCELLED
            $table->timestamp('issued_at')->nullable();
            $table->date('expires_at')->nullable();        // ikut masa berlaku periode keanggotaan

            // Info penukaran terakhir (riwayat lengkap ada di redemption_requests)
            $table->foreignId('redeemed_by')->nullable()->constrained('users')->nullOnDelete();       // manajer approving
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();      // staff pengaju
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();         // hotel/unit penukaran
            $table->string('outlet', 100)->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('expired_marked_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index('voucher_type_id');
        });

        // Riwayat permintaan penukaran (staff mengajukan -> manajer menyetujui/menolak)
        Schema::create('redemption_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('voucher_type_id')->constrained('voucher_types');
            $table->string('voucher_no', 30);
            $table->foreignId('requested_by')->constrained('users');           // staff (tidak boleh finalisasi sendiri)
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();
            $table->string('outlet', 100)->nullable();
            $table->string('status', 20)->default('PENDING_APPROVAL'); // PENDING_APPROVAL | APPROVED | REJECTED
            $table->text('request_note')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete(); // manajer
            $table->timestamp('decided_at')->nullable();
            $table->text('rejection_reason')->nullable();            // riwayat penolakan + alasan wajib terjaga
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('requested_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemption_requests');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('voucher_package_items');
        Schema::dropIfExists('voucher_packages');
        Schema::dropIfExists('voucher_types');
    }
};
