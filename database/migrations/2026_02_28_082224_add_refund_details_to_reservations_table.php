<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->integer('stripe_fee')->nullable()->after('refunded_amount');
            $table->integer('platform_fee_refund_amount')->nullable()->after('stripe_fee');
            $table->integer('owner_net_amount')->nullable()->after('platform_fee_refund_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['stripe_fee', 'platform_fee_refund_amount', 'owner_net_amount']);
        });
    }
};
