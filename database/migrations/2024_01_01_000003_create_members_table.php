<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_no', 20)->unique();      // HCM-000182
            $table->foreignId('hotel_id')->constrained('hotels');
            $table->string('membership_type', 10);          // paid | free
            $table->foreignId('level_id')->constrained('membership_levels');

            // Personal data
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 30);
            $table->date('dob')->nullable();
            $table->string('gender', 10)->nullable();       // male | female
            $table->text('address')->nullable();
            $table->string('id_number', 50)->nullable();    // KTP / Passport
            $table->string('id_type', 20)->nullable();      // ktp | passport | sim | other
            $table->string('company')->nullable();
            $table->string('occupation')->nullable();

            // Status & agregat
            $table->string('status', 30)->default('pending_review'); // pending_review, pending_payment, active, inactive, expired
            $table->unsignedInteger('total_visits')->default(0);
            $table->decimal('total_spending', 15, 2)->default(0);
            $table->date('joined_at')->nullable();
            $table->date('valid_until')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email']);
            $table->index(['phone']);
            $table->index(['id_number']);
            $table->index(['full_name', 'dob']);
        });

        // Benefit yang diberikan ke member (mis. voucher)
        Schema::create('member_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('benefit_id')->constrained('membership_benefits')->cascadeOnDelete();
            $table->string('status', 20)->default('active'); // active, redeemed, expired
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->foreignId('redeemed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_benefits');
        Schema::dropIfExists('members');
    }
};
