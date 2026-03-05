<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin ユーザーの作成
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@rsvbase.jp')],
            [
                'name' => 'システム管理者',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'role' => 'admin',
            ]
        );

        // デモ用オーナーの作成
        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'デモオーナー',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'stripe_account_status' => 'complete',
                'stripe_account_id' => 'acct_demo12345678',
            ]
        );

        // デモ用施設の作成
        $facility = $owner->facilities()->firstOrCreate(
            ['name' => 'サンプル民宿 海の家'],
            [
                'description' => '海を一望できる小さな民宿です。',
                'postal_code' => '299-2403',
                'address' => '千葉県南房総市富浦町多田良1212',
                'phone' => '0470-33-1234',
                'email' => 'info@sample-minshuku.example.com',
                'same_day_booking_cutoff_time' => '18:00:00',
                'check_in_start_time' => '15:00:00',
                'check_in_end_time' => '22:00:00',
                'cancellation_policy_text' => "【キャンセルポリシー】\n・7日前まで: 無料\n・3〜6日前: 宿泊料の30%\n・前日: 宿泊料の50%\n・当日/無連絡: 宿泊料の100%",
                'terms_text' => '当施設の宿泊約款に同意いただいた上でご予約ください。',
                'platform_fee_rate' => 0.05,
                'is_published' => true,
            ]
        );

        // デモ用部屋の作成
        $facility->rooms()->firstOrCreate(
            ['name' => '和室 海の間'],
            [
                'description' => '6畳の和室。海が見えるお部屋です。',
                'capacity' => 3,
                'base_price_per_night' => 10000,
                'weekend_price_per_night' => 13000,
                'cleaning_fee' => 3000,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $facility->rooms()->firstOrCreate(
            ['name' => '洋室 星の間'],
            [
                'description' => 'ツインベッドの洋室。星空観察に最適です。',
                'capacity' => 2,
                'base_price_per_night' => 12000,
                'weekend_price_per_night' => 15000,
                'cleaning_fee' => 3000,
                'sort_order' => 2,
                'is_active' => true,
            ]
        );
    }
}
