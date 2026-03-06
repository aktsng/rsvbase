<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\RoomRequest;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Traits\HasCurrentFacility;
use App\Traits\HasImageProcessing;

class RoomController extends Controller
{
    use HasCurrentFacility, HasImageProcessing;

    /**
     * 表示
     */
    public function index(Request $request)
    {
        $facility = $this->getCurrentFacility($request);

        $rooms = $facility->rooms()->orderBy('created_at', 'desc')->get();

        return Inertia::render('Owner/Room/Index', [
            'rooms' => $rooms,
            'facility_is_published' => (bool) $facility->is_published,
        ]);
    }

    /**
     * 作成フォーム
     */
    public function create()
    {
        return Inertia::render('Owner/Room/Create');
    }

    /**
     * 保存
     */
    public function store(RoomRequest $request)
    {
        $facility = $this->getCurrentFacility($request);

        $data = $request->validated();

        // 室単価の場合は子供関連・追加料金フィールドをリセット
        if (($data['pricing_type'] ?? 'room') === 'room') {
            // 室単価でもラベル等は保持し、追加料金のみリセット
            $data['add_child_a_fee'] = 0;
            $data['add_child_b_fee'] = 0;
        }

        $room = new Room($data);
        $room->facility_id = $facility->id;
        $room->is_active = $request->boolean('is_active', true);

        // 画像処理
        for ($i = 1; $i <= 3; $i++) {
            if ($request->hasFile("image{$i}")) {
                $room->{"image{$i}_path"} = $this->processAndStoreImage($request->file("image{$i}"), 'rooms');
            }
        }

        $room->save();

        return redirect()->route('owner.rooms.index')->with('success', '新しい部屋を登録しました。');
    }

    /**
     * 編集フォーム
     */
    public function edit(Request $request, Room $room)
    {
        $facility = $this->getCurrentFacility($request);

        // 自分の施設の部屋かチェック
        if ($room->facility_id !== $facility->id) {
            abort(403);
        }

        return Inertia::render('Owner/Room/Edit', [
            'room' => $room,
        ]);
    }

    /**
     * 更新
     */
    public function update(RoomRequest $request, Room $room)
    {
        $facility = $this->getCurrentFacility($request);

        if ($room->facility_id !== $facility->id) {
            abort(403);
        }

        $data = $request->validated();

        // 室単価の場合は子供関連・追加料金フィールドをリセット
        if (($data['pricing_type'] ?? 'room') === 'room') {
            // 室単価でもラベル等は保持し、追加料金のみリセット
            $data['add_child_a_fee'] = 0;
            $data['add_child_b_fee'] = 0;
        }

        // 画像処理
        for ($i = 1; $i <= 3; $i++) {
            if ($request->hasFile("image{$i}")) {
                // 旧画像の削除
                if ($room->{"image{$i}_path"}) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($room->{"image{$i}_path"});
                }
                $data["image{$i}_path"] = $this->processAndStoreImage($request->file("image{$i}"), 'rooms');
            }
        }

        $room->update($data);

        return redirect()->route('owner.rooms.index')->with('success', '部屋情報を更新しました。');
    }

    /**
     * 削除
     */
    public function destroy(Request $request, Room $room)
    {
        $facility = $this->getCurrentFacility($request);

        if ($room->facility_id !== $facility->id) {
            abort(403);
        }

        // 予約済みの部屋は削除不可に
        if ($room->reservations()->exists()) {
            return back()->with('error', '予約が存在する部屋は削除できません。');
        }

        // 画像の削除
        for ($i = 1; $i <= 3; $i++) {
            if ($room->{"image{$i}_path"}) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($room->{"image{$i}_path"});
            }
        }

        $room->delete();

        return redirect()->route('owner.rooms.index')->with('success', '部屋を削除しました。');
    }

}
