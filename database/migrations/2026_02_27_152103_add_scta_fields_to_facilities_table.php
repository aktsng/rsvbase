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
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('scta_business_name')->nullable()->after('terms_text');
            $table->string('scta_representative')->nullable()->after('scta_business_name');
            $table->string('scta_address')->nullable()->after('scta_representative');
            $table->string('scta_contact_email')->nullable()->after('scta_address');
            $table->text('scta_contact_tel_disclaimer')->nullable()->after('scta_contact_email');
            $table->text('scta_price_description')->nullable()->after('scta_contact_tel_disclaimer');
            $table->text('scta_payment_method_time')->nullable()->after('scta_price_description');
            $table->text('scta_service_delivery_time')->nullable()->after('scta_payment_method_time');
            $table->text('scta_cancellation_return')->nullable()->after('scta_service_delivery_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn([
                'scta_business_name',
                'scta_representative',
                'scta_address',
                'scta_contact_email',
                'scta_contact_tel_disclaimer',
                'scta_price_description',
                'scta_payment_method_time',
                'scta_service_delivery_time',
                'scta_cancellation_return',
            ]);
        });
    }
};
