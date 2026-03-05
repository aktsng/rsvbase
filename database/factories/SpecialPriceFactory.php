<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\SpecialPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpecialPriceFactory extends Factory
{
    protected $model = SpecialPrice::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 month', '+3 months');
        $endDate = (clone $startDate)->modify('+3 days');

        return [
            'room_id' => Room::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'price_per_night' => $this->faker->randomElement([20000, 25000, 30000, 35000]),
            'label' => $this->faker->randomElement(['年末年始', 'GW', 'お盆', '連休特別']),
        ];
    }
}
