<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Facility;
use App\Models\Room;
use App\Services\BookingPriceCalculatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCompletedGuest;
use App\Mail\ReservationCompletedOwner;

use App\Traits\HasCurrentFacility;

class ReservationController extends Controller
{
    use HasCurrentFacility;

    /**
     * 予約一覧の表示
     */
    public function index(Request $request)
    {
        $facility = $this->getCurrentFacility($request);


        // 検索やフィルタ機能（ステータス）
        $status = $request->query('status', 'all'); // 'all', 'confirmed', 'cancelled', 'refunded'
        $search = $request->query('search', '');

        $query = $facility->reservations()->with('room')->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                    ->orWhere('guest_email', 'like', "%{$search}%")
                    ->orWhere('guest_phone', 'like', "%{$search}%")
                    ->orWhere('reservation_code', 'like', "%{$search}%");
            });
        }

        $reservations = $query->with('room:id,name')
            ->select([
                'reservations.id',
                'reservations.uuid',
                'reservations.room_id',
                'reservations.reservation_code',
                'reservations.guest_name',
                'reservations.number_of_guests',
                'reservations.check_in_date',
                'reservations.check_out_date',
                'reservations.total_amount',
                'reservations.status',
                'reservations.guest_remarks',
                'reservations.created_at'
            ])
            ->latest('reservations.created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($res) => [
                'uuid' => $res->uuid,
                'reservation_code' => $res->reservation_code,
                'guest_name' => $res->guest_name,
                'number_of_guests' => $res->number_of_guests,
                'room_name' => $res->room->name,
                'check_in_date' => $res->check_in_date->format('Y-m-d'),
                'check_out_date' => $res->check_out_date->format('Y-m-d'),
                'total_amount' => $res->total_amount,
                'status' => $res->status,
                'refunded_amount' => $res->refunded_amount,
                'stripe_fee' => $res->stripe_fee,
                'platform_fee_refund_amount' => $res->platform_fee_refund_amount,
                'owner_net_amount' => $res->owner_net_amount,
                'guest_remarks' => $res->guest_remarks,
                'created_at' => $res->created_at->format('Y-m-d H:i'),
                'platform_fee_rate' => (float) ($res->platform_fee_rate ?? $facility->platform_fee_rate ?? 0.05),
                'payment_method' => $res->payment_method,
                'booking_source' => $res->booking_source,
            ]);

        return Inertia::render('Owner/Reservation/Index', [
            'reservations' => $reservations,
            'rooms' => $facility->rooms()->select('id', 'name', 'capacity', 'uuid', 'pricing_type', 'child_a_label', 'child_a_policy', 'child_b_label', 'child_b_policy')->get(),
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'statusLabels' => [
                'confirmed' => '確定済み',
                'cancelled' => '取消',
                'refunded' => '返金済み',
            ],
            'platform_fee_rate' => (float) ($facility->platform_fee_rate ?? 0.05),
        ]);
    }

    /**
     * 予約詳細の表示
     */
    public function show(Request $request, Reservation $reservation)
    {
        $facility = $this->getCurrentFacility($request);

        if ($reservation->room->facility_id !== $facility->id) {
            abort(403);
        }

        // 部屋の料金タイプ情報を取得
        $room = $reservation->room;

        return Inertia::render('Owner/Reservation/Show', [
            'reservation' => [
                'uuid' => $reservation->uuid,
                'reservation_code' => $reservation->reservation_code,
                'room_name' => $room->name,
                'guest_name' => $reservation->guest_name,
                'guest_email' => $reservation->guest_email,
                'guest_phone' => $reservation->guest_phone,
                'guest_remarks' => $reservation->guest_remarks,
                'owner_memo' => $reservation->owner_memo,
                'number_of_guests' => $reservation->number_of_guests,
                'number_of_adults' => $reservation->number_of_adults ?? $reservation->number_of_guests,
                'number_of_child_a' => $reservation->number_of_child_a ?? 0,
                'number_of_child_b' => $reservation->number_of_child_b ?? 0,
                'check_in_date' => $reservation->check_in_date->format('Y/m/d'),
                'check_out_date' => $reservation->check_out_date->format('Y/m/d'),
                'total_amount' => $reservation->total_amount,
                'status' => $reservation->status,
                'refunded_amount' => $reservation->refunded_amount,
                'stripe_fee' => $reservation->stripe_fee,
                'platform_fee_refund_amount' => $reservation->platform_fee_refund_amount,
                'owner_net_amount' => $reservation->owner_net_amount,
                'stripe_payment_intent_id' => $reservation->stripe_payment_intent_id,
                'created_at' => $reservation->created_at->format('Y/m/d H:i'),
                'booked_price_details' => collect($reservation->booked_price_details)->map(function ($detail) use ($room) {
                    return array_merge($detail, [
                        'addChildAFee' => $detail['addChildAFee'] ?? $room->add_child_a_fee ?? 0,
                        'addChildBFee' => $detail['addChildBFee'] ?? $room->add_child_b_fee ?? 0,
                    ]);
                }),
                'booked_cleaning_fee' => $reservation->booked_cleaning_fee,
                'platform_fee_rate' => (float) ($reservation->platform_fee_rate ?? $facility->platform_fee_rate ?? 0.05),
                'payment_method' => $reservation->payment_method,
                'booking_source' => $reservation->booking_source,
                // 料金タイプ情報
                'pricing_type' => $room->pricing_type ?? 'room',
                'child_a_label' => $room->child_a_label,
                'child_b_label' => $room->child_b_label,
            ],
            'platform_fee_rate' => (float) ($facility->platform_fee_rate ?? 0.05),
        ]);
    }

    /**
     * オーナーメモの更新
     */
    public function update(Request $request, Reservation $reservation)
    {
        $facility = $this->getCurrentFacility($request);

        if ($reservation->room->facility_id !== $facility->id) {
            abort(403);
        }

        $validated = $request->validate([
            'owner_memo' => ['nullable', 'string', 'max:5000'],
        ]);

        $reservation->update($validated);

        return back()->with('success', 'オーナーメモを更新しました。');
    }

    /**
     * 予約のキャンセル・全額返金（オーナー操作用）
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        $facility = $this->getCurrentFacility($request);

        // 施設マスタのIDチェック
        if ($reservation->room->facility_id !== $facility->id) {
            abort(403);
        }

        if ($reservation->status !== 'confirmed') {
            return back()->with('error', 'この予約はすでにキャンセルされているか、確定していません。');
        }

        $validated = $request->validate([
            'refund_amount' => ['required', 'integer', 'min:0', "max:{$reservation->total_amount}"],
            'cancel_without_refund' => ['nullable', 'boolean'],
        ]);

        $refundAmount = $validated['cancel_without_refund'] ? 0 : $validated['refund_amount'];

        // 手数料等の計算と記録用の値作成
        if ($reservation->payment_method === 'onsite') {
            $stripeFee = 0;
            $platformFeeRefund = 0;
            $ownerNetAmount = $reservation->total_amount - $refundAmount;
        } else {
            $stripeFee = (int) ceil($reservation->total_amount * 0.036);
            // 手数料率を取得（古い予約などで未設定の場合は施設設定または5%）
            $currentRate = $reservation->platform_fee_rate ?? $facility->platform_fee_rate ?? 0.05;
            // プラットフォームが返金に充てるのは、自身の取り分（1.4%相当）のみとする
            $systemFeeRate = max(0, $currentRate - 0.036);
            $platformFeeRefund = (int) floor($refundAmount * $systemFeeRate);
            $ownerNetAmount = $reservation->total_amount - $refundAmount - $stripeFee + $platformFeeRefund;

            // StripeのRefund APIを呼び出す（返金額が0より大きい場合のみ）
            if ($refundAmount > 0) {
                try {
                    $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

                    // Payment Intentから最新のCharge IDを取得
                    $pi = $stripe->paymentIntents->retrieve($reservation->stripe_payment_intent_id);
                    $chargeId = $pi->latest_charge;

                    // ChargeからApplication Fee IDを取得
                    $charge = $stripe->charges->retrieve($chargeId);
                    $appFeeId = $charge->application_fee;

                    // 1. メインの返金（顧客へ）を実行
                    $stripe->refunds->create([
                        'payment_intent' => $reservation->stripe_payment_intent_id,
                        'amount' => $refundAmount,
                        'reverse_transfer' => true,
                        'refund_application_fee' => false, // 自動比例返金は使用しない
                    ]);

                    // 2. システム利用料分のみを別途払い戻す（AppFeeが存在し、返戻額がある場合）
                    if ($appFeeId && $platformFeeRefund > 0) {
                        $stripe->applicationFees->createRefund($appFeeId, [
                            'amount' => $platformFeeRefund,
                        ]);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Stripe Refund Error: ' . $e->getMessage());
                    return back()->with('error', 'Stripeでの返金処理に失敗しました。詳細：' . $e->getMessage());
                }
            }
        }

        $newStatus = ($refundAmount > 0 && $reservation->payment_method !== 'onsite') ? 'refunded' : 'cancelled';

        $reservation->update([
            'status' => $newStatus,
            'refunded_amount' => $refundAmount,
            'stripe_fee' => $stripeFee,
            'platform_fee_refund_amount' => $platformFeeRefund,
            'owner_net_amount' => $ownerNetAmount,
        ]);

        // キャンセル（返金）メール送信のディスパッチ（Stripe連携済みの場合のみ）
        if ($request->user()->stripe_account_status === 'complete') {
            try {
                if ($reservation->guest_email) {
                    if ($reservation->payment_method === 'onsite') {
                        \Illuminate\Support\Facades\Mail::to($reservation->guest_email)
                            ->send(new \App\Mail\ReservationCancelled($reservation));
                    } elseif ($refundAmount > 0) {
                        \Illuminate\Support\Facades\Mail::to($reservation->guest_email)
                            ->send(new \App\Mail\ReservationRefunded($reservation));
                    } else {
                        \Illuminate\Support\Facades\Mail::to($reservation->guest_email)
                            ->send(new \App\Mail\ReservationCancelled($reservation));
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Cancel Email Error: ' . $e->getMessage());
            }
        }

        $successMsg = $refundAmount > 0
            ? '返金処理を実施し、ステータスを返金済みに更新しました。通知メールも送信されました。'
            : '返金なし（キャンセル料100%）で予約を取り消しました。通知メールも送信されました。';

        return back()->with('success', $successMsg);
    }

    /**
     * 手動予約の登録（現地決済）
     */
    public function store(Request $request)
    {
        $facility = $this->getCurrentFacility($request);

        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'number_of_adults' => ['nullable', 'integer', 'min:1'],
            'number_of_child_a' => ['nullable', 'integer', 'min:0'],
            'number_of_child_b' => ['nullable', 'integer', 'min:0'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:50'],
            'guest_remarks' => ['nullable', 'string', 'max:1000'],
            'owner_memo' => ['nullable', 'string', 'max:5000'],
        ], [
            'check_in_date.after_or_equal' => 'チェックイン日は今日以降の日付を指定してください。',
            'check_out_date.after' => 'チェックアウト日はチェックイン日より後の日付を指定してください（同日は不可）。',
            'number_of_guests.min' => '宿泊人数は1名以上で入力してください。',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // 施設所有チェック
        if ($room->facility_id !== $facility->id) {
            abort(403);
        }

        $adults = $validated['number_of_adults'] ?? $validated['number_of_guests'];
        $childA = $validated['number_of_child_a'] ?? 0;
        $childB = $validated['number_of_child_b'] ?? 0;

        // 定員計算（カウント対象のみ合算）
        $occupancy = $adults
            + ($room->child_a_is_counted ? $childA : 0)
            + ($room->child_b_is_counted ? $childB : 0);

        // 定員チェック
        if ($occupancy > $room->capacity) {
            return back()->withErrors(['number_of_guests' => "この部屋の定員は{$room->capacity}名です。"])->with('error', "定員（カウント対象: {$occupancy}名 / 最大: {$room->capacity}名）を超えています。");
        }

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);

        // 重複チェック (キャンセル済み以外を対象)
        $isReserved = $room->reservations()
            ->where('status', '!=', 'cancelled')
            ->overlapping($checkIn, $checkOut)
            ->exists();

        if ($isReserved) {
            return back()->withErrors(['check_in_date' => '指定された日程は既に予約されています。'])->with('error', '指定された日程は既に予約されています。');
        }

        $isBlocked = $room->blocks()
            ->where('blocked_date', '>=', $checkIn->toDateString())
            ->where('blocked_date', '<', $checkOut->toDateString())
            ->exists();

        if ($isBlocked) {
            return back()->withErrors(['check_in_date' => '指定された日程はブロックされています。'])->with('error', '指定された日程はブロックされています。');
        }

        // 料金計算
        $calcService = new BookingPriceCalculatorService();
        $priceBreakdown = $calcService->calculate($room, $checkIn, $checkOut, $adults, $childA, $childB);

        // 予約作成
        $reservation = new Reservation([
            'room_id' => $room->id,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'status' => 'confirmed',
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'check_in_time' => $facility->check_in_start_time ?? '15:00:00',
            'number_of_guests' => $adults + $childA + $childB,
            'number_of_adults' => $adults,
            'number_of_child_a' => $childA,
            'number_of_child_b' => $childB,
            'guest_remarks' => $validated['guest_remarks'],
            'owner_memo' => $validated['owner_memo'],
            'booked_price_details' => $priceBreakdown->toBookedPriceDetails(),
            'booked_cleaning_fee' => $room->cleaning_fee ?? 0,
            'total_amount' => $priceBreakdown->totalAmount,
            'platform_fee_rate' => $facility->platform_fee_rate ?? 0.05,
            'platform_fee' => 0, // 現地決済のためシステム利用料は0
            'payment_method' => 'onsite',
            'booking_source' => 'manual',
        ]);

        $reservation->save();

        // メール通知（Stripe連携済みの場合のみ送信）
        if ($request->user()->stripe_account_status === 'complete') {
            try {
                if ($reservation->guest_email) {
                    Mail::to($reservation->guest_email)->send(new ReservationCompletedGuest($reservation));
                }
                Mail::to($facility->owner->email)->send(new ReservationCompletedOwner($reservation));
            } catch (\Exception $e) {
                Log::error('Manual Reservation Email Error: ' . $e->getMessage());
            }
        }

        return redirect()->route('owner.reservations.index')->with('success', '電話予約を登録しました。');
    }

    /**
     * 予約料金の計算（確認画面用）
     */
    public function calculatePrice(Request $request)
    {
        $facility = $this->getCurrentFacility($request);

        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'number_of_adults' => ['nullable', 'integer', 'min:1'],
            'number_of_child_a' => ['nullable', 'integer', 'min:0'],
            'number_of_child_b' => ['nullable', 'integer', 'min:0'],
        ]);

        $room = Room::findOrFail($validated['room_id']);

        if ($room->facility_id !== $facility->id) {
            abort(403);
        }

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $adults = $validated['number_of_adults'] ?? $validated['number_of_guests'];
        $childA = $validated['number_of_child_a'] ?? 0;
        $childB = $validated['number_of_child_b'] ?? 0;

        // 定員計算（カウント対象のみ合算）
        $occupancy = $adults
            + ($room->child_a_is_counted ? $childA : 0)
            + ($room->child_b_is_counted ? $childB : 0);

        // 定員チェック
        if ($occupancy > $room->capacity) {
            return response()->json([
                'error' => "この部屋の定員は{$room->capacity}名です。現在の入力では定員対象が{$occupancy}名となっています。"
            ], 422);
        }

        // 重複チェック
        $isReserved = $room->reservations()
            ->where('status', '!=', 'cancelled')
            ->overlapping($checkIn, $checkOut)
            ->exists();

        if ($isReserved) {
            return response()->json(['error' => '指定された日程は既に予約されています。'], 422);
        }

        $isBlocked = $room->blocks()
            ->where('blocked_date', '>=', $checkIn->toDateString())
            ->where('blocked_date', '<', $checkOut->toDateString())
            ->exists();

        if ($isBlocked) {
            return response()->json(['error' => '指定された日程はブロックされています。'], 422);
        }

        $calcService = new BookingPriceCalculatorService();
        $priceBreakdown = $calcService->calculate($room, $checkIn, $checkOut, $adults, $childA, $childB);

        return response()->json([
            'daily_prices' => $priceBreakdown->toBookedPriceDetails(),
            'cleaning_fee' => $priceBreakdown->cleaningFee,
            'total_amount' => $priceBreakdown->totalAmount,
            'subtotal' => $priceBreakdown->subtotal(),
            'pricing_type' => $room->pricing_type ?? 'room',
            'adults' => $adults,
            'child_a' => $childA,
            'child_b' => $childB,
            'add_child_a_fee' => $priceBreakdown->addChildAFee,
            'add_child_b_fee' => $priceBreakdown->addChildBFee,
            'child_a_label' => $room->child_a_label,
            'child_b_label' => $room->child_b_label,
        ]);
    }
    /**
     * 部屋の空き状況を取得（カレンダー表示用）
     */
    public function getAvailability(Request $request, Room $room)
    {
        $facility = $this->getCurrentFacility($request);

        if ($room->facility_id !== $facility->id) {
            abort(403);
        }

        // 前後半年程度のデータを取得
        $start = now()->subMonth()->startOfMonth();
        $end = now()->addMonths(6)->endOfMonth();

        $reservations = $room->reservations()
            ->where('status', '!=', 'cancelled')
            ->where('check_in_date', '<', $end)
            ->where('check_out_date', '>', $start)
            ->get();

        $blocks = $room->blocks()
            ->whereBetween('blocked_date', [$start, $end])
            ->get();

        $availability = [];

        // 予約済みのマッピング
        foreach ($reservations as $res) {
            for ($d = $res->check_in_date->copy(); $d->lt($res->check_out_date); $d->addDay()) {
                $availability[$d->toDateString()] = ['type' => 'reserved'];
            }
        }

        // ブロックのマッピング
        foreach ($blocks as $block) {
            $availability[$block->blocked_date->toDateString()] = ['type' => 'blocked'];
        }

        return response()->json($availability);
    }
}
