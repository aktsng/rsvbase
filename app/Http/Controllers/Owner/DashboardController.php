<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Traits\HasCurrentFacility;

class DashboardController extends Controller
{
    use HasCurrentFacility;

    public function index(Request $request)
    {
        $facility = $this->getCurrentFacility($request);

        // --- カレンダーデータの加工作業 ---
        $monthStr = $request->query('month', now()->format('Y-m'));
        try {
            $currentMonth = \Carbon\Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = now()->startOfMonth();
        }

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $rooms = $facility->rooms()->where('is_active', true)->orderBy('sort_order')->get();

        // 施設全体の今月の予約を取得
        $allReservations = $facility->reservations()
            ->with('room')
            ->confirmed()
            ->where('check_in_date', '<', $endOfMonth->copy()->addDay())
            ->where('check_out_date', '>', $startOfMonth)
            ->get();

        // 部屋のブロック情報を取得
        $roomBlocks = \App\Models\RoomBlock::whereIn('room_id', $rooms->pluck('id'))
            ->whereBetween('blocked_date', [$startOfMonth, $endOfMonth])
            ->get();

        $calendarDays = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $calendarDays[] = [
                'date' => $date->toDateString(),
                'day' => $date->day,
                'day_label' => ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek],
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
                'is_saturday' => $date->isSaturday(),
                'is_sunday' => $date->isSunday(),
            ];
        }

        $roomReservations = [];
        foreach ($rooms as $room) {
            $mappedRes = [];
            foreach ($allReservations->where('room_id', $room->id) as $res) {
                $nights = $res->check_in_date->diffInDays($res->check_out_date);

                // チェックインからチェックアウト前日までを「予約あり」とする
                for ($d = $res->check_in_date->copy(); $d->lt($res->check_out_date); $d->addDay()) {
                    $dateStr = $d->toDateString();
                    if ($d->between($startOfMonth, $endOfMonth)) {
                        $isFirstDay = $d->isSameDay($res->check_in_date);
                        $isLastDay = $d->copy()->addDay()->isSameDay($res->check_out_date);

                        if ($isFirstDay && $isLastDay) {
                            $position = 'single';
                        } elseif ($isFirstDay) {
                            $position = 'start';
                        } elseif ($isLastDay) {
                            $position = 'end';
                        } else {
                            $position = 'middle';
                        }

                        $mappedRes[$dateStr] = [
                            'type' => 'reserved',
                            'uuid' => $res->uuid,
                            'guest_name' => $res->guest_name,
                            'reservation_id' => $res->id,
                            'nights' => $nights,
                            'position' => $position,
                        ];
                    }
                }
            }

            // ブロックされている日を追加
            foreach ($roomBlocks->where('room_id', $room->id) as $block) {
                $dateStr = $block->blocked_date->toDateString();
                if (!isset($mappedRes[$dateStr])) {
                    $mappedRes[$dateStr] = [
                        'type' => 'blocked',
                    ];
                }
            }
            $roomReservations[] = [
                'id' => $room->id,
                'uuid' => $room->uuid,
                'name' => $room->name,
                'capacity' => $room->capacity,
                'days' => $mappedRes,
            ];
        }

        $recentReservations = $allReservations
            ->sortByDesc('created_at')
            ->take(10)
            ->map(fn($r) => [
                'uuid' => $r->uuid,
                'reservation_code' => $r->reservation_code,
                'guest_name' => $r->guest_name,
                'room_name' => $r->room->name,
                'check_in_date' => $r->check_in_date->format('Y/m/d'),
                'check_out_date' => $r->check_out_date->format('Y/m/d'),
                'total_amount' => $r->total_amount,
                'number_of_guests' => $r->number_of_guests,
                'created_at' => $r->created_at->format('Y/m/d H:i'),
            ])->values();

        $stats = [
            'total_reservations' => $allReservations->count(),
            'total_revenue' => $allReservations->sum('total_amount'),
        ];

        return Inertia::render('Owner/Dashboard', [
            'facility' => [
                'uuid' => $facility->uuid,
                'name' => $facility->name,
                'is_published' => $facility->is_published,
                'public_url' => route('guest.booking.show', $facility->slug ?: $facility->uuid),
                'stripe_account_status' => $request->user()->stripe_account_status,
            ],
            'rooms' => $rooms->map(fn($r) => [
                'id' => $r->id,
                'uuid' => $r->uuid,
                'name' => $r->name,
                'capacity' => $r->capacity,
                'pricing_type' => $r->pricing_type,
                'child_a_label' => $r->child_a_label,
                'child_a_policy' => $r->child_a_policy,
                'child_b_label' => $r->child_b_label,
                'child_b_policy' => $r->child_b_policy,
            ]),
            'stats' => $stats,
            'recentReservations' => $recentReservations,
            'calendar' => [
                'currentMonth' => $currentMonth->format('Y-m'),
                'prevMonth' => $currentMonth->copy()->subMonth()->format('Y-m'),
                'nextMonth' => $currentMonth->copy()->addMonth()->format('Y-m'),
                'days' => $calendarDays,
                'rooms' => $roomReservations,
            ],
            'announcements' => Announcement::pinnedDashboard()
                ->orderByDesc('published_at')
                ->take(5)
                ->get()
                ->map(fn($a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'body' => $a->body,
                    'category' => $a->category,
                    'category_label' => $a->category_label,
                    'category_color' => $a->category_color,
                    'published_at' => $a->published_at->format('Y/m/d'),
                ]),
        ]);
    }
}
