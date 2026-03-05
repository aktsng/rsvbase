<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Facility;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReservationController extends Controller
{
    /**
     * 予約一覧の表示
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['room.facility.owner']);

        // 検索: 予約番号
        if ($request->filled('code')) {
            $query->where('reservation_code', 'like', "%{$request->code}%");
        }

        // 検索: オーナー (ID)
        if ($request->filled('owner')) {
            $ownerId = $request->owner;
            $query->whereHas('room.facility', function ($q) use ($ownerId) {
                $q->where('owner_id', $ownerId);
            });
        }

        // 検索: 施設 (UUID)
        if ($request->filled('facility')) {
            $facilityUuid = $request->facility;
            $query->whereHas('room.facility', function ($q) use ($facilityUuid) {
                $q->where('uuid', $facilityUuid);
            });
        }

        // 検索: 受付日（予約日）
        if ($request->filled('booked_from')) {
            $query->whereDate('created_at', '>=', $request->booked_from);
        }
        if ($request->filled('booked_to')) {
            $query->whereDate('created_at', '<=', $request->booked_to);
        }

        // 検索: 宿泊日
        if ($request->filled('stay_from')) {
            // チェックインが期間内、または滞在期間が被っているもの
            $query->where('check_out_date', '>', $request->stay_from);
        }
        if ($request->filled('stay_to')) {
            $query->where('check_in_date', '<', $request->stay_to);
        }

        $reservations = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($r) => [
                'uuid' => $r->uuid,
                'reservation_code' => $r->reservation_code,
                'guest_name' => $r->guest_name,
                'guest_email' => $r->guest_email,
                'facility_name' => $r->room->facility->name,
                'owner_name' => $r->room->facility->owner->name,
                'room_name' => $r->room->name,
                'check_in_date' => $r->check_in_date->format('Y/m/d'),
                'check_out_date' => $r->check_out_date->format('Y/m/d'),
                'total_amount' => $r->total_amount,
                'status' => $r->status,
                'payment_method' => $r->payment_method,
                'created_at' => $r->created_at->format('Y/m/d H:i'),
            ]);

        $owners = User::where('role', 'owner')->orderBy('name')->get(['id', 'name']);
        $facilities = Facility::orderBy('name')->get(['uuid', 'name']);

        return Inertia::render('Admin/Reservations/Index', [
            'reservations' => $reservations,
            'owners' => $owners,
            'facilities' => $facilities,
            'filters' => $request->only(['code', 'owner', 'facility', 'booked_from', 'booked_to', 'stay_from', 'stay_to']),
        ]);
    }

    /**
     * 予約詳細の表示
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['room.facility.owner']);

        return Inertia::render('Admin/Reservations/Show', [
            'reservation' => [
                'uuid' => $reservation->uuid,
                'reservation_code' => $reservation->reservation_code,
                'status' => $reservation->status,
                'payment_method' => $reservation->payment_method,
                'booking_source' => $reservation->booking_source,
                'guest_name' => $reservation->guest_name,
                'guest_email' => $reservation->guest_email,
                'guest_phone' => $reservation->guest_phone,
                'number_of_guests' => $reservation->number_of_guests,
                'guest_remarks' => $reservation->guest_remarks,
                'check_in_date' => $reservation->check_in_date->format('Y-m-d'),
                'check_out_date' => $reservation->check_out_date->format('Y-m-d'),
                'check_in_time' => $reservation->check_in_time,
                'facility_uuid' => $reservation->room->facility->uuid ?? null,
                'facility_name' => $reservation->room->facility->name ?? '不明',
                'owner_name' => $reservation->room->facility->owner->name ?? '不明',
                'room_name' => $reservation->room->name ?? '不明',
                'pricing_type' => $reservation->room->pricing_type ?? 'room',
                'child_a_label' => $reservation->room->child_a_label,
                'child_b_label' => $reservation->room->child_b_label,
                'number_of_adults' => $reservation->number_of_adults ?? $reservation->number_of_guests,
                'number_of_child_a' => $reservation->number_of_child_a ?? 0,
                'number_of_child_b' => $reservation->number_of_child_b ?? 0,
                'booked_price_details' => collect($reservation->booked_price_details)->map(function ($detail) use ($reservation) {
                    return array_merge($detail, [
                        'addChildAFee' => $detail['addChildAFee'] ?? $reservation->room->add_child_a_fee ?? 0,
                        'addChildBFee' => $detail['addChildBFee'] ?? $reservation->room->add_child_b_fee ?? 0,
                    ]);
                }),
                'booked_cleaning_fee' => $reservation->booked_cleaning_fee,
                'total_amount' => $reservation->total_amount,
                // Stripe・手数料情報
                'platform_fee_rate' => $reservation->platform_fee_rate,
                'platform_fee' => $reservation->platform_fee,
                'stripe_fee' => $reservation->stripe_fee,
                'stripe_payment_intent_id' => $reservation->stripe_payment_intent_id,
                'owner_net_amount' => $reservation->owner_net_amount,
                // 返金情報
                'refunded_amount' => $reservation->refunded_amount,
                'stripe_refund_id' => $reservation->stripe_refund_id,
                'platform_fee_refund_amount' => $reservation->platform_fee_refund_amount,

                'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $reservation->updated_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
