<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();

            // 宿泊情報
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('status')->default('confirmed'); // confirmed, canceled

            // ゲスト情報
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone');
            $table->time('check_in_time');
            $table->unsignedSmallInteger('number_of_guests');
            $table->text('guest_remarks')->nullable();

            // 財務スナップショット（予約確定時にハードコピー）
            $table->json('booked_price_details'); // 日別単価と適用理由の配列
            $table->unsignedInteger('booked_cleaning_fee');
            $table->unsignedInteger('total_amount'); // 総額（円）
            $table->decimal('platform_fee_rate', 5, 4); // 予約時の手数料率スナップショット
            $table->unsignedInteger('platform_fee'); // プラットフォーム手数料額（円）
            $table->unsignedInteger('refunded_amount')->default(0); // 返金済額

            // Stripe関連
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->string('stripe_refund_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'check_in_date', 'check_out_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
