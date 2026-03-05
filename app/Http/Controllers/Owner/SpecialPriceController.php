<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\SpecialPriceRequest;
use App\Models\Room;
use App\Models\SpecialPrice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpecialPriceController extends Controller
{
    public function index(Request $request, Room $room)
    {
        if ($room->facility->user_id !== $request->user()->id) {
            abort(403);
        }

        // 未来の特定日料金を取得
        $specialPrices = $room->specialPrices()
            ->where('end_date', '>=', now()->toDateString())
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(fn($sp) => [
                'id' => $sp->id,
                'start_date' => $sp->start_date->format('Y-m-d'),
                'end_date' => $sp->end_date->format('Y-m-d'),
                'price_per_night' => $sp->price_per_night,
                'label' => $sp->label,
            ]);

        return Inertia::render('Owner/Room/SpecialPrice/Index', [
            'room' => [
                'uuid' => $room->uuid,
                'name' => $room->name,
                'base_price_per_night' => $room->base_price_per_night,
                'weekend_price_per_night' => $room->weekend_price_per_night,
            ],
            'specialPrices' => $specialPrices,
        ]);
    }

    public function store(SpecialPriceRequest $request, Room $room)
    {
        if ($room->facility->user_id !== $request->user()->id) {
            abort(403);
        }

        $room->specialPrices()->create($request->validated());

        return back()->with('success', '特定日料金を追加しました。');
    }

    public function destroy(Request $request, SpecialPrice $special_price)
    {
        if ($special_price->room->facility->user_id !== $request->user()->id) {
            abort(403);
        }

        $special_price->delete();

        return back()->with('success', '特定日料金を削除しました。');
    }
}
