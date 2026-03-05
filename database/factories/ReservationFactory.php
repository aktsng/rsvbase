<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $checkIn = $this->faker->dateTimeBetween('+1 week', '+2 months');
        $nights = $this->faker->numberBetween(1, 4);
        $checkOut = (clone $checkIn)->modify("+{$nights} days");
        $pricePerNight = $this->faker->randomElement([10000, 12000, 15000]);
        $cleaningFee = 3000;
        $totalAmount = $pricePerNight * $nights + $cleaningFee;
        $platformFeeRate = 0.05;
        $platformFee = (int) floor($totalAmount * $platformFeeRate);

        $dailyDetails = [];
        $date = clone $checkIn;
        for ($i = 0; $i < $nights; $i++) {
            $dailyDetails[] = [
                'date' => $date->format('Y-m-d'),
                'price' => $pricePerNight,
                'label' => '基本料金',
            ];
            $date->modify('+1 day');
        }

        return [
            'uuid' => Str::uuid()->toString(),
            'room_id' => Room::factory(),
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'status' => 'confirmed',
            'guest_name' => $this->faker->name(),
            'guest_email' => $this->faker->safeEmail(),
            'guest_phone' => $this->faker->phoneNumber(),
            'check_in_time' => '15:00',
            'number_of_guests' => $this->faker->numberBetween(1, 4),
            'guest_remarks' => $this->faker->optional(0.3)->sentence(),
            'booked_price_details' => $dailyDetails,
            'booked_cleaning_fee' => $cleaningFee,
            'total_amount' => $totalAmount,
            'platform_fee_rate' => $platformFeeRate,
            'platform_fee' => $platformFee,
            'refunded_amount' => 0,
            'stripe_payment_intent_id' => 'pi_' . Str::random(24),
        ];
    }

    public function canceled(): static
    {
        return $this->state(fn() => ['status' => 'canceled']);
    }
}
