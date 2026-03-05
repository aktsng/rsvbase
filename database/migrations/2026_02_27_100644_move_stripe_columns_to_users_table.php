<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. users テーブルに Stripe 関連カラムを追加
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_account_id')->nullable()->after('role');
            $table->string('stripe_account_status')->default('pending')->after('stripe_account_id');
        });

        // 2. 既存のデータを移行 (facility にデータがある場合、そのオーナーに紐付ける)
        $facilities = DB::table('facilities')
            ->whereNotNull('stripe_account_id')
            ->get();

        foreach ($facilities as $facility) {
            DB::table('users')
                ->where('id', $facility->user_id)
                ->update([
                    'stripe_account_id' => $facility->stripe_account_id,
                    'stripe_account_status' => $facility->stripe_account_status,
                ]);
        }

        // 3. facilities テーブルから Stripe 関連カラムを削除
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn(['stripe_account_id', 'stripe_account_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('stripe_account_id')->nullable()->after('email');
            $table->string('stripe_account_status')->default('pending')->after('stripe_account_id');
        });

        // データの戻し
        $users = DB::table('users')
            ->whereNotNull('stripe_account_id')
            ->get();

        foreach ($users as $user) {
            DB::table('facilities')
                ->where('user_id', $user->id)
                ->update([
                    'stripe_account_id' => $user->stripe_account_id,
                    'stripe_account_status' => $user->stripe_account_status,
                ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_account_id', 'stripe_account_status']);
        });
    }
};
