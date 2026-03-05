<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            if (!Schema::hasColumn('facilities', 'check_out_time')) {
                $table->time('check_out_time')->default('10:00:00')->after('check_in_end_time');
            }
            if (!Schema::hasColumn('facilities', 'booking_cutoff_hours')) {
                $table->integer('booking_cutoff_hours')->default(0)->after('same_day_booking_cutoff_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn(['check_out_time', 'booking_cutoff_hours']);
        });
    }
};
