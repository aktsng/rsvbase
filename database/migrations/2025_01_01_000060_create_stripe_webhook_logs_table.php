<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stripe_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique(); // 冪等性担保用
            $table->string('event_type');
            $table->json('payload');
            $table->unsignedSmallInteger('status_code')->default(200);
            $table->text('error_message')->nullable();
            $table->string('processing_status')->default('received'); // received, processed, failed
            $table->timestamps();

            $table->index('event_type');
            $table->index('processing_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_logs');
    }
};
