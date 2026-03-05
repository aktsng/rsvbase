<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // 料金タイプ
            $table->string('pricing_type')->default('room')->after('is_active'); // 'room' or 'person'
            $table->unsignedSmallInteger('min_guests')->default(1)->after('pricing_type'); // 最小宿泊人数

            // 子供A設定
            $table->string('child_a_label')->nullable()->after('min_guests');
            $table->string('child_a_policy')->nullable()->after('child_a_label');
            $table->boolean('child_a_is_counted')->default(true)->after('child_a_policy');

            // 子供B設定
            $table->string('child_b_label')->nullable()->after('child_a_is_counted');
            $table->string('child_b_policy')->nullable()->after('child_b_label');
            $table->boolean('child_b_is_counted')->default(false)->after('child_b_policy');

            // 追加料金（人数単価用）
            $table->unsignedInteger('add_adult_fee')->default(0)->after('child_b_is_counted');
            $table->unsignedInteger('add_child_a_fee')->default(0)->after('add_adult_fee');
            $table->unsignedInteger('add_child_b_fee')->default(0)->after('add_child_a_fee');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'pricing_type',
                'min_guests',
                'child_a_label',
                'child_a_policy',
                'child_a_is_counted',
                'child_b_label',
                'child_b_policy',
                'child_b_is_counted',
                'add_adult_fee',
                'add_child_a_fee',
                'add_child_b_fee',
            ]);
        });
    }
};
