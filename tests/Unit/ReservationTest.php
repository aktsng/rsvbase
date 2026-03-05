<?php

namespace Tests\Unit;

use App\Models\Reservation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * owner_memo が正しく保存されることをテスト
     */
    public function test_it_can_save_owner_memo(): void
    {
        $reservation = new Reservation([
            'room_id' => 1,
            'check_in_date' => '2025-01-01',
            'check_out_date' => '2025-01-02',
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '090-0000-0000',
            'check_in_time' => '15:00',
            'number_of_guests' => 2,
            'booked_price_details' => [],
            'booked_cleaning_fee' => 0,
            'total_amount' => 10000,
            'platform_fee_rate' => 0.05,
            'platform_fee' => 500,
            'owner_memo' => 'This is an owner memo.',
        ]);

        $this->assertEquals('This is an owner memo.', $reservation->owner_memo);
    }
}
