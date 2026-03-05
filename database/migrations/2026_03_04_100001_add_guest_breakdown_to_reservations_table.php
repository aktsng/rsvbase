<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedSmallInteger('number_of_adults')->default(1)->after('number_of_guests');
            $table->unsignedSmallInteger('number_of_child_a')->default(0)->after('number_of_adults');
            $table->unsignedSmallInteger('number_of_child_b')->default(0)->after('number_of_child_a');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'number_of_adults',
                'number_of_child_a',
                'number_of_child_b',
            ]);
        });
    }
};
