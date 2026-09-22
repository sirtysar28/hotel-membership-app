<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CPC v2.0 — penyesuaian tabel lama:
 * - members : kode negara HP (registrasi internasional), tanggal aktivitas terakhir,
 *             penanda downgrade tier (aturan ketidakaktifan 12 bulan), bukti fisik accounting
 * - users   : akun staff/manajer butuh approval (update #18)
 * - transactions : flag kelayakan aktivitas CPC (configurable per jenis)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('phone_country_code', 8)->default('+62')->after('phone'); // +62, +65, +81, ...
            $table->date('last_activity_date')->nullable()->after('valid_until');    // reset periode ketidakaktifan
            $table->timestamp('tier_downgraded_at')->nullable()->after('last_activity_date');
            $table->string('proof_reference', 80)->nullable()->after('occupation');  // bukti fisik dari accounting
            $table->index('last_activity_date');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('approved')->after('is_active'); // approved | pending | rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('eligible_activity')->default(true)->after('counts_as_visit'); // flag kelayakan CPC (configurable)
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['phone_country_code', 'last_activity_date', 'tier_downgraded_at', 'proof_reference']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at']);
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('eligible_activity');
        });
    }
};
