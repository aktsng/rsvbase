<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Room;
use App\Services\BookingPriceCalculatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestController extends Controller
{
    /**
     * 予約ページの表示
     */
    public function show(string $identifier)
    {
        // 施設を uuid または slug で検索（公開中であれば表示）
        $facility = Facility::where(function ($query) use ($identifier) {
            $query->where('uuid', $identifier)->orWhere('slug', $identifier);
        })
            ->where('is_published', true)
            ->firstOrFail();

        // 301 リダイレクト判定: slug が設定されているのに uuid でアクセスされた場合
        if ($facility->slug && $identifier === $facility->uuid) {
            return redirect()->route('guest.booking.show', $facility->slug, 301);
        }

        // Stripe未連携の場合はプレビューモード（予約不可）
        $isPreviewMode = $facility->owner->stripe_account_status !== 'complete';

        // 施設内の person 型部屋から子供設定を取得
        $childSettings = null;
        $hasPersonPricing = false;
        $personRoom = $facility->rooms()
            ->where('pricing_type', 'person')
            ->whereNotNull('child_a_label')
            ->first();
        if ($personRoom) {
            $hasPersonPricing = true;
            $childSettings = [
                'child_a_label' => $personRoom->child_a_label,
                'child_a_policy' => $personRoom->child_a_policy,
                'child_b_label' => $personRoom->child_b_label,
                'child_b_policy' => $personRoom->child_b_policy,
            ];
        } else {
            $hasPersonPricing = $facility->rooms()->where('pricing_type', 'person')->exists();
        }

        return Inertia::render('Guest/Booking', [
            'facility' => [
                'uuid' => $facility->uuid,
                'slug' => $facility->slug,
                'name' => $facility->name,
                'name_en' => $facility->name_en,
                'description' => $facility->description,
                'description_en' => $facility->description_en,
                'address' => $facility->address,
                'address_en' => $facility->address_en,
                'check_in_start_time' => substr($facility->check_in_start_time, 0, 5),
                'check_in_end_time' => substr($facility->check_in_end_time, 0, 5),
                'check_out_time' => substr($facility->check_out_time, 0, 5),
                'cancellation_policy_text' => $facility->cancellation_policy_text,
                'terms_text' => $facility->terms_text,
                'booking_cutoff_hours' => $facility->booking_cutoff_hours,
                // SCTAフィールド追加
                'scta_business_name' => $facility->scta_business_name,
                'scta_representative' => $facility->scta_representative,
                'scta_address' => $facility->scta_address,
                'scta_contact_email' => $facility->scta_contact_email,
                'scta_contact_tel_disclaimer' => $facility->scta_contact_tel_disclaimer,
                'scta_price_description' => $facility->scta_price_description,
                'scta_payment_method_time' => $facility->scta_payment_method_time,
                'scta_service_delivery_time' => $facility->scta_service_delivery_time,
                'email' => $facility->email,
                'phone' => $facility->phone,
                'image_url' => $facility->image_url,
                'image_url_2' => $facility->image_url_2,
                'image_url_3' => $facility->image_url_3,
            ],
            'is_preview_mode' => $isPreviewMode,
            // プレビューモード時はStripeキーを渡さない
            'stripe_key' => $isPreviewMode ? null : config('services.stripe.key'),
            'stripe_account' => $isPreviewMode ? null : $facility->owner->stripe_account_id,
            // 料金タイプ関連
            'has_person_pricing' => $hasPersonPricing,
            'child_settings' => $childSettings,
        ]);
    }

    /**
     * 法的表記（特定商取引法に基づく表記）ページの表示
     */
    public function legal(string $identifier)
    {
        $facility = Facility::where(function ($query) use ($identifier) {
            $query->where('uuid', $identifier)->orWhere('slug', $identifier);
        })
            ->where('is_published', true)
            ->firstOrFail();

        // 301 リダイレクト判定
        if ($facility->slug && $identifier === $facility->uuid) {
            return redirect()->route('guest.legal', $facility->slug, 301);
        }

        return Inertia::render('Guest/Legal', [
            'facility' => [
                'uuid' => $facility->uuid,
                'slug' => $facility->slug,
                'name' => $facility->name,
                'name_en' => $facility->name_en,
                'address' => $facility->address,
                'address_en' => $facility->address_en,
                'email' => $facility->email,
                'phone' => $facility->phone,
                'cancellation_policy_text' => $facility->cancellation_policy_text,
                'terms_text' => $facility->terms_text,
                // SCTAフィールド追加
                'scta_business_name' => $facility->scta_business_name,
                'scta_representative' => $facility->scta_representative,
                'scta_address' => $facility->scta_address,
                'scta_contact_email' => $facility->scta_contact_email,
                'scta_contact_tel_disclaimer' => $facility->scta_contact_tel_disclaimer,
                'scta_price_description' => $facility->scta_price_description,
                'scta_payment_method_time' => $facility->scta_payment_method_time,
                'scta_service_delivery_time' => $facility->scta_service_delivery_time,
                'image_url' => $facility->image_url,
                'image_url_2' => $facility->image_url_2,
                'image_url_3' => $facility->image_url_3,
            ]
        ]);
    }

    /**
     * 空室検索と料金計算API
     */
    public function apiAvailability(Request $request, string $identifier)
    {
        $validated = $request->validate([
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'guests' => ['nullable', 'integer', 'min:1', 'max:20'],
            'adults' => ['nullable', 'integer', 'min:1', 'max:20'],
            'child_a' => ['nullable', 'integer', 'min:0', 'max:20'],
            'child_b' => ['nullable', 'integer', 'min:0', 'max:20'],
        ]);

        $facility = Facility::where('uuid', $identifier)->orWhere('slug', $identifier)->firstOrFail();

        // 予約受付の締切チェック (施設側設定の cutoff_hours)
        $now = now();
        $checkInDateTime = Carbon::parse($validated['check_in_date'] . ' ' . $facility->check_in_start_time);
        if ($now->diffInHours($checkInDateTime, false) < $facility->booking_cutoff_hours) {
            return response()->json([
                'success' => false,
                'message' => 'このチェックイン日の予約受付は終了しています。',
            ], 400);
        }

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        // adults が未指定の場合は guests をフォールバック（既存Booking.vue互換）
        $adults = $validated['adults'] ?? $validated['guests'] ?? 1;
        $childA = $validated['child_a'] ?? 0;
        $childB = $validated['child_b'] ?? 0;

        // 有効な部屋を取得
        $availableRooms = [];
        $rooms = $facility->rooms()->where('is_active', true)->get();

        $calcService = new BookingPriceCalculatorService();

        foreach ($rooms as $room) {
            // 定員チェック: 部屋の pricing_type によって計算方法を分岐
            if ($room->isPersonPricing()) {
                // 人数単価: is_counted フラグで定員加算を判定
                $occupancy = $adults
                    + ($room->child_a_is_counted ? $childA : 0)
                    + ($room->child_b_is_counted ? $childB : 0);
            } else {
                // 室単価: 全員カウント
                $occupancy = $adults + $childA + $childB;
            }

            if ($occupancy > $room->capacity) {
                continue;
            }

            // min_guests チェック
            if ($adults < ($room->min_guests ?? 1)) {
                continue;
            }

            // ブロックチェック
            $isBlocked = $room->blocks()
                ->where('blocked_date', '>=', $checkIn->toDateString())
                ->where('blocked_date', '<', $checkOut->toDateString())
                ->exists();

            if ($isBlocked)
                continue;

            // 予約チェック
            $isReserved = $room->reservations()
                ->confirmed()
                ->where('check_in_date', '<', $checkOut->toDateString())
                ->where('check_out_date', '>', $checkIn->toDateString())
                ->exists();

            if ($isReserved)
                continue;

            // 料金計算
            $priceBreakdown = $calcService->calculate($room, $checkIn, $checkOut, $adults, $childA, $childB);

            $roomData = [
                'uuid' => $room->uuid,
                'name' => $room->name,
                'capacity' => $room->capacity,
                'description' => $room->description,
                'total_amount' => $priceBreakdown->totalAmount,
                'nightly_total' => $priceBreakdown->subtotal(),
                'cleaning_fee' => $priceBreakdown->cleaningFee,
                'nights_count' => count($priceBreakdown->dailyPrices),
                'image_urls' => $room->image_urls,
                // 料金タイプ情報
                'pricing_type' => $room->pricing_type ?? 'room',
                'min_guests' => $room->min_guests ?? 1,
                'daily_prices' => $priceBreakdown->toBookedPriceDetails(),
            ];

            // 人数単価の場合は詳細情報を追加
            if ($room->isPersonPricing()) {
                $roomData['add_child_a_fee'] = $room->add_child_a_fee ?? 0;
                $roomData['add_child_b_fee'] = $room->add_child_b_fee ?? 0;
                $roomData['child_a_label'] = $room->child_a_label;
                $roomData['child_a_policy'] = $room->child_a_policy;
                $roomData['child_a_is_counted'] = $room->child_a_is_counted;
                $roomData['child_b_label'] = $room->child_b_label;
                $roomData['child_b_policy'] = $room->child_b_policy;
                $roomData['child_b_is_counted'] = $room->child_b_is_counted;
                // 1泊あたりの基本料金（人数分の内訳表示用）
                $firstDay = array_values($priceBreakdown->dailyPrices)[0] ?? null;
                $roomData['base_price_per_night'] = $firstDay ? $firstDay['price'] : $room->base_price_per_night;
            }

            $availableRooms[] = $roomData;
        }

        return response()->json([
            'success' => true,
            'rooms' => $availableRooms,
        ]);
    }

    /**
     * Stripe PaymentIntent の生成
     */
    public function createPaymentIntent(Request $request, string $identifier)
    {
        $validated = $request->validate([
            'room_uuid' => ['required', 'string'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'guests' => ['nullable', 'integer', 'min:1'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'child_a' => ['nullable', 'integer', 'min:0'],
            'child_b' => ['nullable', 'integer', 'min:0'],
        ]);

        $facility = Facility::where('uuid', $identifier)->orWhere('slug', $identifier)->firstOrFail();
        $room = $facility->rooms()->where('uuid', $validated['room_uuid'])->firstOrFail();

        $adults = $validated['adults'] ?? $validated['guests'] ?? 1;
        $childA = $validated['child_a'] ?? 0;
        $childB = $validated['child_b'] ?? 0;

        // 再計算して実際の支払額を確定
        $calcService = new BookingPriceCalculatorService();
        $priceBreakdown = $calcService->calculate(
            $room,
            Carbon::parse($validated['check_in_date']),
            Carbon::parse($validated['check_out_date']),
            $adults,
            $childA,
            $childB,
        );

        $totalAmount = $priceBreakdown->totalAmount;

        // 手数料率を取得（未設定の場合は5%）
        $feeRate = $facility->platform_fee_rate ?? 0.05;
        $platformFee = (int) round($totalAmount * $feeRate);

        $paymentIntentData = [
            'amount' => $totalAmount,
            'currency' => 'jpy',
            'payment_method_types' => ['card'],
            'metadata' => [
                'facility_uuid' => $facility->uuid,
                'room_uuid' => $room->uuid,
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'adults' => $adults,
                'child_a' => $childA,
                'child_b' => $childB,
            ],
        ];

        // テスト環境のダミーID(acct_demo...)でない、かつ正しいStripeアカウントIDが登録されている場合のみConnectの譲渡データをセット
        $stripeAccountId = $facility->owner->stripe_account_id;
        if (!empty($stripeAccountId) && str_starts_with($stripeAccountId, 'acct_') && !str_contains($stripeAccountId, 'demo')) {
            $paymentIntentData['application_fee_amount'] = $platformFee;
            $paymentIntentData['transfer_data'] = [
                'destination' => $stripeAccountId,
            ];
        }

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        try {
            $paymentIntent = $stripe->paymentIntents->create($paymentIntentData);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'total_amount' => $totalAmount,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Stripe PaymentIntent Error: ' . $e->getMessage());
            return response()->json(['error' => '決済の準備に失敗しました: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 決済完了後、予約の保存
     */
    public function storeReservation(Request $request, string $identifier)
    {
        $validated = $request->validate([
            'payment_intent_id' => ['required', 'string'],
            'room_uuid' => ['required', 'string'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date'],
            'guests' => ['nullable', 'integer', 'min:1'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'child_a' => ['nullable', 'integer', 'min:0'],
            'child_b' => ['nullable', 'integer', 'min:0'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:50'],
            'guest_remarks' => ['nullable', 'string', 'max:1000'],
            'total_amount' => ['required', 'integer'],
        ]);

        $facility = Facility::where('uuid', $identifier)->orWhere('slug', $identifier)->with('owner')->firstOrFail();
        $room = $facility->rooms()->where('uuid', $validated['room_uuid'])->firstOrFail();

        $adults = $validated['adults'] ?? $validated['guests'] ?? 1;
        $childA = $validated['child_a'] ?? 0;
        $childB = $validated['child_b'] ?? 0;

        // 予約の作成（決済は済んでいる想定）
        // ※再計算して明細を取得
        $calcService = new \App\Services\BookingPriceCalculatorService();
        $priceBreakdown = $calcService->calculate(
            $room,
            \Carbon\Carbon::parse($validated['check_in_date']),
            \Carbon\Carbon::parse($validated['check_out_date']),
            $adults,
            $childA,
            $childB,
        );

        $platformFeeRate = $facility->platform_fee_rate ?? 0.05;

        $reservation = new \App\Models\Reservation([
            'room_id' => $room->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'guest_remarks' => $validated['guest_remarks'],
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'check_in_time' => $facility->check_in_start_time ?? '15:00:00',
            'number_of_guests' => $adults + $childA + $childB,
            'number_of_adults' => $adults,
            'number_of_child_a' => $childA,
            'number_of_child_b' => $childB,
            // 料金関連
            'booked_price_details' => $priceBreakdown->toBookedPriceDetails(),
            'booked_cleaning_fee' => $room->cleaning_fee ?? 0,
            'total_amount' => $validated['total_amount'],
            'platform_fee_rate' => $platformFeeRate,
            'platform_fee' => (int) round($validated['total_amount'] * $platformFeeRate),
            // Stripe
            'stripe_payment_intent_id' => $validated['payment_intent_id'],
            'status' => 'confirmed',
        ]);

        $reservation->save();
        \Illuminate\Support\Facades\Log::info('Reservation saved', ['id' => $reservation->id, 'uuid' => $reservation->uuid]);

        // メール通知（同期またはキュー）
        try {
            \Illuminate\Support\Facades\Log::info('Sending reservation emails...');
            \Illuminate\Support\Facades\Mail::to($reservation->guest_email)
                ->send(new \App\Mail\ReservationCompletedGuest($reservation));

            \Illuminate\Support\Facades\Mail::to($facility->owner->email)
                ->send(new \App\Mail\ReservationCompletedOwner($reservation));
            \Illuminate\Support\Facades\Log::info('Reservation emails sent successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Reservation Email Error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'reservation_uuid' => $reservation->uuid,
        ]);
    }
}
