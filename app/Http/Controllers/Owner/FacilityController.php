<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\FacilityRequest;
use App\Models\Facility;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Traits\HasCurrentFacility;
use App\Traits\HasImageProcessing;

class FacilityController extends Controller
{
    use HasCurrentFacility, HasImageProcessing;

    public function create(Request $request)
    {
        // 複数施設対応のため、常に作成可能にするか、制限するかは運用次第。
        // ここでは一旦そのまま
        if ($request->user()->facilities()->exists()) {

            return redirect()->route('owner.dashboard');
        }

        return Inertia::render('Owner/Facility/Create');
    }

    public function store(FacilityRequest $request)
    {
        if ($request->user()->facilities()->exists()) {
            return redirect()->route('owner.dashboard');
        }

        $data = $request->validated();

        // カラム名の命名不整合を解消 (updateと同様のロジック)
        if (isset($data['check_in_time_start'])) {
            $data['check_in_start_time'] = $data['check_in_time_start'];
        }
        if (isset($data['check_in_time_end'])) {
            $data['check_in_end_time'] = $data['check_in_time_end'];
        }
        if (array_key_exists('cancellation_policy', $data)) {
            $data['cancellation_policy_text'] = $data['cancellation_policy'];
            unset($data['cancellation_policy']);
        }

        unset($data['check_in_time_start']);
        unset($data['check_in_time_end']);

        $data['is_published'] = false; // 初回は必ず非公開

        $request->user()->facilities()->create($data);

        return redirect()->route('owner.dashboard')->with('success', '施設を登録しました。続いて部屋情報を設定してください。');
    }

    public function edit(Request $request)
    {
        $facility = $this->getCurrentFacility($request);

        return Inertia::render('Owner/Facility/Edit', [
            'facility' => [
                'uuid' => $facility->uuid,
                'name' => $facility->name,
                'name_en' => $facility->name_en,
                'description' => $facility->description,
                'description_en' => $facility->description_en,
                'address' => $facility->address,
                'address_en' => $facility->address_en,
                'phone' => $facility->phone,
                'booking_cutoff_hours' => $facility->booking_cutoff_hours,
                'check_in_time_start' => $facility->check_in_start_time,
                'check_in_time_end' => $facility->check_in_end_time,
                'check_out_time' => $facility->check_out_time,
                'cancellation_policy' => $facility->cancellation_policy_text,
                'terms_text' => $facility->terms_text,
                'is_published' => $facility->is_published,
                'stripe_account_status' => $request->user()->stripe_account_status,
                'scta_business_name' => $facility->scta_business_name,
                'scta_representative' => $facility->scta_representative,
                'scta_address' => $facility->scta_address,
                'scta_contact_email' => $facility->scta_contact_email,
                'scta_contact_tel_disclaimer' => $facility->scta_contact_tel_disclaimer,
                'scta_price_description' => $facility->scta_price_description,
                'scta_payment_method_time' => $facility->scta_payment_method_time,
                'scta_service_delivery_time' => $facility->scta_service_delivery_time,
                'slug' => $facility->slug,
                'image_url' => $facility->image_url,
                'image_url_2' => $facility->image_url_2,
                'image_url_3' => $facility->image_url_3,
            ],
        ]);
    }

    public function update(FacilityRequest $request)
    {
        $facility = $this->getCurrentFacility($request);

        // 必須のStripeアカウントが連携されていないと公開できない制限 → プレビューモードに移行したため廃止
        $validated = $request->validated();

        // カラム名の命名不整合を解消
        $data = $validated;

        // チェックイン・アウト時間のマッピング (Frontend -> DB)
        if (isset($data['check_in_time_start'])) {
            $data['check_in_start_time'] = $data['check_in_time_start'];
        }
        if (isset($data['check_in_time_end'])) {
            $data['check_in_end_time'] = $data['check_in_time_end'];
        }

        // cancellation_policy -> cancellation_policy_text
        if (array_key_exists('cancellation_policy', $data)) {
            $data['cancellation_policy_text'] = $data['cancellation_policy'];
            unset($data['cancellation_policy']);
        }

        // 不要なキーを除去
        unset($data['check_in_time_start']);
        unset($data['check_in_time_end']);

        // slugが既に設定されている場合は、変更を禁止する
        if (!empty($facility->slug)) {
            unset($data['slug']);
        }


        if ($request->hasFile('image')) {
            // 旧画像の削除
            if ($facility->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($facility->image_path);
            }
            $path = $this->processAndStoreImage($request->file('image'), 'facilities');
            $data['image_path'] = $path;
        }

        if ($request->hasFile('image_2')) {
            if ($facility->image_path_2) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($facility->image_path_2);
            }
            $path = $this->processAndStoreImage($request->file('image_2'), 'facilities');
            $data['image_path_2'] = $path;
        }

        if ($request->hasFile('image_3')) {
            if ($facility->image_path_3) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($facility->image_path_3);
            }
            $path = $this->processAndStoreImage($request->file('image_3'), 'facilities');
            $data['image_path_3'] = $path;
        }

        $facility->update($data);

        return redirect()->route('owner.facility.edit')->with('success', '施設情報を更新しました。');
    }
}
