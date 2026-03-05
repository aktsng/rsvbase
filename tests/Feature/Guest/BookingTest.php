<?php

namespace Tests\Feature\Guest;

use App\Models\Facility;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCompletedGuest;
use App\Mail\ReservationCompletedOwner;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 備考欄付きで予約ができることをテスト
     */
    public function test_guest_can_book_with_remarks(): void
    {
        Mail::fake();

        $facility = Facility::factory()->create(['is_published' => true]);
        $room = Room::factory()->create(['facility_id' => $facility->id]);

        $response = $this->from(route('guest.booking.show', $facility->uuid))
            ->post(route('api.reservations.store', $facility->uuid), [
                'room_uuid' => $room->uuid,
                'check_in_date' => '2025-05-01',
                'check_out_date' => '2025-05-03',
                'guest_name' => 'テスト 太郎',
                'guest_email' => 'test@example.com',
                'guest_phone' => '09012345678',
                'check_in_time' => '15:00',
                'guests' => 2,
                'guest_remarks' => 'アレルギーがあります。',
                'total_amount' => 20000,
                'payment_intent_id' => 'pi_test_123',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('reservations', [
            'guest_name' => 'テスト 太郎',
            'guest_remarks' => 'アレルギーがあります。',
        ]);

        Mail::assertSent(ReservationCompletedGuest::class, function ($mail) {
            return $mail->reservation->guest_remarks === 'アレルギーがあります。';
        });

        Mail::assertSent(ReservationCompletedOwner::class, function ($mail) {
            return $mail->reservation->guest_remarks === 'アレルギーがあります。';
        });
    }
}
