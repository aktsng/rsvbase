<?php

namespace Database\Factories;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'user_id' => User::factory(),
            'name' => $this->faker->company() . ' Inn',
            'description' => $this->faker->sentence(),
            'postal_code' => $this->faker->postcode(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'same_day_booking_cutoff_time' => '18:00:00',
            'check_in_start_time' => '15:00:00',
            'check_in_end_time' => '22:00:00',
            'cancellation_policy_text' => 'チェックイン7日前まで無料キャンセル可能。',
            'terms_text' => '宿泊約款の内容をここに記載します。',
            'platform_fee_rate' => 0.05,
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn() => ['is_published' => false]);
    }
}
