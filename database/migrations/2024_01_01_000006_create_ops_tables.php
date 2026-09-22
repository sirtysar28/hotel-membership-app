<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('card_no', 20);
            $table->string('token', 64)->unique(); // isi QR code
            $table->string('level_name', 50);
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });

        Schema::create('duplicate_checks', function (Blueprint $table) {
            $table->id();
            $table->json('submitted_data');
            $table->foreignId('matched_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->json('matched_fields')->nullable(); // {name: "match", email: "match", ...}
            $table->string('status', 30)->default('potential_duplicate'); // potential_duplicate, confirmed, rejected, merged
            $table->text('resolution_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('model_type', 50)->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('to_email');
            $table->string('type', 40); // welcome, upgrade, renewal, expiration, transaction, payment
            $table->string('subject');
            $table->longText('body');
            $table->boolean('status')->default(true);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // FK untuk users (hotels & members sudah dibuat)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('hotel_id')->references('id')->on('hotels')->nullOnDelete();
            $table->foreign('member_id')->references('id')->on('members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('duplicate_checks');
        Schema::dropIfExists('digital_cards');
    }
};
