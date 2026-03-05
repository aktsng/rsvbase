<?php

namespace Tests\Feature\Owner;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * オーナーが予約一覧で特定の項目が表示されないこと、および「取消」文言を確認
     */
    public function test_owner_can_see_guest_remarks_but_not_phone_email_in_list(): void
    {
        $user = User::factory()->create();
        $facility = Facility::factory()->create(['user_id' => $user->id]);
        $room = Room::factory()->create(['facility_id' => $facility->id]);
        $reservation = Reservation::factory()->create([
            'room_id' => $room->id,
            'status' => 'confirmed',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '090-1111-2222',
            'guest_remarks' => 'Special request here.',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['current_facility_uuid' => $facility->uuid])
            ->get(route('owner.reservations.index'));

        $response->assertStatus(200);
        $response->assertSee('Special request here.');
        $response->assertDontSee('090-1111-2222');
        $response->assertDontSee('guest@example.com');

        // Assert props directly for Inertia
        $props = $response->original->getData()['page']['props'];
        $this->assertEquals('取消', $props['statusLabels']['cancelled']);

        $reservationData = $props['reservations']['data'][0];
        $this->assertArrayNotHasKey('guest_email', $reservationData);
        $this->assertArrayNotHasKey('guest_phone', $reservationData);
    }

    /**
     * オーナーが予約詳細を表示し、メモを更新できることをテスト
     */
    public function test_owner_can_view_reservation_and_update_memo(): void
    {
        $user = User::factory()->create();
        $facility = Facility::factory()->create(['user_id' => $user->id]);
        $room = Room::factory()->create(['facility_id' => $facility->id]);
        $reservation = Reservation::factory()->create(['room_id' => $room->id]);

        // 詳細画面の表示
        $response = $this->actingAs($user)
            ->withSession(['current_facility_uuid' => $facility->uuid])
            ->get(route('owner.reservations.show', $reservation->uuid));
        $response->assertStatus(200);

        // メモの更新
        $response = $this->actingAs($user)
            ->withSession(['current_facility_uuid' => $facility->uuid])
            ->put(route('owner.reservations.update', $reservation->uuid), [
                'owner_memo' => 'Updated owner memo text.',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'owner_memo' => 'Updated owner memo text.',
        ]);
    }
}
