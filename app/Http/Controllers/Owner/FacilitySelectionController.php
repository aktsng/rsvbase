<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;

class FacilitySelectionController extends Controller
{
    /**
     * 管理する施設を切り替える
     */
    public function select(Request $request)
    {
        $validated = $request->validate([
            'uuid' => 'required|string|exists:facilities,uuid',
        ]);

        $facility = $request->user()->facilities()
            ->where('uuid', $validated['uuid'])
            ->firstOrFail();

        // 選択された施設IDをセッションに保持
        $request->session()->put('current_facility_uuid', $facility->uuid);

        return back()->with('success', "管理施設を「{$facility->name}」に切り替えました。");
    }
}
