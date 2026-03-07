<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomBlock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request, Room $room)
    {
        if ($room->facility->user_id !== $request->user()->id) {
            abort(403);
        }

        $monthStr = $request->query('month', now()->format('Y-m'));
        try {
            $currentMonth = Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = now()->startOfMonth();
        }

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // 予約済みの取得（期間が重なるものを取得）
        $reservations = $room->reservations()
            ->confirmed()
            ->where('check_in_date', '<', $endOfMonth->copy()->addDay()) // チェックアウト日は含めない（当日チェックイン可能なため）
            ->where('check_out_date', '>', $startOfMonth)
            ->get();

        // 手動ブロックの取得
        $blocks = $room->blocks()
            ->whereBetween('blocked_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn($b) => $b->blocked_date->toDateString());

        // 日付データの構築
        $days = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dateStr = $date->toDateString();

            // 該当日の予約を見つける
            $resAtDate = $reservations->first(function ($res) use ($dateStr) {
                return $dateStr >= $res->check_in_date->toDateString() && $dateStr < $res->check_out_date->toDateString();
            });

            $dayData = [
                'date' => $dateStr,
                'day_number' => $date->day,
                'is_today' => $date->isToday(),
                'is_past' => $date->isPast() && !$date->isToday(),
                'is_reserved' => (bool) $resAtDate,
                'is_blocked' => $blocks->has($dateStr),
            ];

            if ($resAtDate) {
                // 時刻の影響を排除して宿泊数を計算
                $checkIn = Carbon::parse($resAtDate->check_in_date->toDateString());
                $checkOut = Carbon::parse($resAtDate->check_out_date->toDateString());
                $currentDate = Carbon::parse($dateStr);

                $nights = (int) $checkIn->diffInDays($checkOut);
                $dayDiff = (int) $checkIn->diffInDays($currentDate);

                $dayData['reservation_day_number'] = $dayDiff + 1;

                if ($nights === 1) {
                    $dayData['reservation_position'] = 'single';
                } elseif ($currentDate->isSameDay($checkIn)) {
                    $dayData['reservation_position'] = 'start';
                } elseif ($currentDate->copy()->addDay()->isSameDay($checkOut)) {
                    $dayData['reservation_position'] = 'end';
                } else {
                    $dayData['reservation_position'] = 'middle';
                }
            }

            $days[] = $dayData;
        }

        return Inertia::render('Owner/Room/Calendar/Index', [
            'room' => [
                'uuid' => $room->uuid,
                'name' => $room->name,
            ],
            'currentMonth' => $currentMonth->format('Y-m'),
            'prevMonth' => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentMonth->copy()->addMonth()->format('Y-m'),
            'days' => $days,
        ]);
    }

    public function toggleBlock(Request $request, Room $room)
    {
        if ($room->facility->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'action' => ['required', 'in:block,unblock'],
        ]);

        $dateStr = Carbon::parse($validated['date'])->toDateString();

        // 予約があるか確認（ブロックする場合のみ）
        if ($validated['action'] === 'block') {
            $hasReservation = $room->reservations()
                ->confirmed()
                ->where('check_in_date', '<=', $dateStr)
                ->where('check_out_date', '>', $dateStr)
                ->exists();

            if ($hasReservation) {
                return back()->with('error', '予約済みの日はブロックできません。');
            }

            // ブロック登録
            RoomBlock::firstOrCreate([
                'room_id' => $room->id,
                'blocked_date' => $dateStr,
            ], [
                'reason' => 'owner_blocked',
            ]);
        } else {
            // ブロック解除
            RoomBlock::where('room_id', $room->id)
                ->where('blocked_date', $dateStr)
                ->delete();
        }

        return back()->with('success', 'カレンダーを更新しました。');
    }
}
