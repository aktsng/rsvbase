<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('stripe_account_id')->nullable();
            $table->string('stripe_account_status')->default('pending'); // pending, active, restricted
            $table->time('same_day_booking_cutoff_time')->default('18:00:00');
            $table->time('check_in_start_time')->default('15:00:00');
            $table->time('check_in_end_time')->default('22:00:00');
            $table->text('cancellation_policy_text')->nullable();
            $table->text('terms_text')->nullable();
            $table->decimal('platform_fee_rate', 5, 4)->default(0.05); // 5%
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
