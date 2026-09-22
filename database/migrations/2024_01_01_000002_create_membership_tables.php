<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Level membership (Classic / Privilege / Signature / Diamond)
        Schema::create('membership_levels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->string('card_color', 20)->default('#1e3a5f');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Rule Engine: threshold per level, configurable dari dashboard
        Schema::create('membership_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('membership_levels')->cascadeOnDelete();
            $table->unsignedInteger('min_visit')->default(0);      // 0 = tidak dipersyaratkan
            $table->unsignedInteger('max_visit')->nullable();      // null = unlimited
            $table->decimal('min_spending', 15, 2)->default(0);    // 0 = tidak dipersyaratkan
            $table->unsignedInteger('evaluation_period_months')->default(0); // 0 = lifetime
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Benefit configurable per level / per hotel
        Schema::create('membership_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->nullable()->constrained('membership_levels')->nullOnDelete();
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();
            $table->string('name');
            $table->string('category', 30)->default('other'); // room, fnb, discount, voucher, complimentary, other
            $table->text('description')->nullable();
            $table->string('value')->nullable();               // mis. "20%", "1x Room Upgrade"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_benefits');
        Schema::dropIfExists('membership_rules');
        Schema::dropIfExists('membership_levels');
    }
};
