<?php

namespace App\Traits;

use App\Models\Facility;
use Illuminate\Http\Request;

trait HasCurrentFacility
{
    /**
     * 現在ログイン中のオーナーが選択している施設を取得する
     */
    protected function getCurrentFacility(Request $request): Facility
    {
        $user = $request->user();
        $uuid = $request->session()->get('current_facility_uuid');

        if ($uuid) {
            $facility = $user->facilities()->where('uuid', $uuid)->first();
            if ($facility) {
                return $facility;
            }
        }

        // セッションにない、または不正な場合は最初の施設をデフォルトとし、セッションを更新
        $facility = $user->facilities()->first();

        if (!$facility) {
            // 施設が1つもないケース（通常はないはずだが念のため）
            abort(redirect()->route('owner.facility.create')->with('error', '管理施設が見つかりません。まず施設を登録してください。'));
        }

        $request->session()->put('current_facility_uuid', $facility->uuid);
        return $facility;
    }
}
