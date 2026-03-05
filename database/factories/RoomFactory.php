<?php

namespace Database\Factories;

use App\Models\Facility;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'facility_id' => Facility::factory(),
            'name' => $this->faker->randomElement(['和室', '洋室', 'ツインルーム', 'デラックス', 'スタンダード']) . $this->faker->numberBetween(101, 305),
            'description' => $this->faker->sentence(),
            'capacity' => $this->faker->numberBetween(1, 6),
            'base_price_per_night' => $this->faker->randomElement([8000, 10000, 12000, 15000, 20000]),
            'weekend_price_per_night' => $this->faker->randomElement([10000, 13000, 15000, 18000, 25000]),
            'cleaning_fee' => $this->faker->randomElement([2000, 3000, 5000]),
            'sort_order' => 0,
            'is_active' => true,
            'pricing_type' => 'room',
            'min_guests' => 1,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }

    public function withoutWeekendPrice(): static
    {
        return $this->state(fn() => ['weekend_price_per_night' => null]);
    }

    /**
     * 人数単価の部屋を作成
     */
    public function personPricing(): static
    {
        return $this->state(fn() => [
            'pricing_type' => 'person',
            'capacity' => 6,
            'min_guests' => 1,
            'add_adult_fee' => $this->faker->randomElement([5000, 7000, 8000]),
            'child_a_label' => '小学生',
            'child_a_policy' => '12歳まで・定員に含む',
            'child_a_is_counted' => true,
            'add_child_a_fee' => $this->faker->randomElement([3000, 4000, 5000]),
            'child_b_label' => '乳幼児',
            'child_b_policy' => '3歳まで・添い寝',
            'child_b_is_counted' => false,
            'add_child_b_fee' => 0,
        ]);
    }
}
