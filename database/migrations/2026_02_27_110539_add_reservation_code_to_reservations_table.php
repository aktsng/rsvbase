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
            $table->string('reservation_code')->nullable()->after('uuid')->index();
        });

        // 既存データのバックフィル
        $reservations = \Illuminate\Support\Facades\DB::table('reservations')->whereNull('reservation_code')->get();
        foreach ($reservations as $res) {
            $code = $this->generateUniqueCode();
            \Illuminate\Support\Facades\DB::table('reservations')
                ->where('id', $res->id)
                ->update(['reservation_code' => $code]);
        }

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('reservation_code')->nullable(false)->unique()->change();
        });
    }

    private function generateUniqueCode(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // 0, 1, I, O, Lを除外
        do {
            $code = 'R-';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[rand(0, strlen($chars) - 1)];
            }
        } while (\Illuminate\Support\Facades\DB::table('reservations')->where('reservation_code', $code)->exists());

        return $code;
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('reservation_code');
        });
    }
};
