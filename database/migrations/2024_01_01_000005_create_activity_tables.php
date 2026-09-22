<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Transaksi hotel stay / F&B / lainnya
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 40)->unique();  // HTL-20260916-001
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels');
            $table->string('type', 20); // hotel_stay, restaurant, bar, banquet, other_fnb, other
            $table->string('outlet')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->boolean('counts_as_visit')->default(true);
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Kunjungan member (hasil redeem)
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->date('visit_date');
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Audit trail redeem: siapa, kapan, data sebelum & sesudah
        Schema::create('visit_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('data_before')->nullable();
            $table->json('data_after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_redemptions');
        Schema::dropIfExists('visits');
        Schema::dropIfExists('transactions');
    }
};
