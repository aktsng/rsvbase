<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class FacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ミドルウェアでOwner判定済み
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'description_en' => ['nullable', 'string', 'max:1000'],
            'address' => ['required', 'string', 'max:255'],
            'address_en' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'booking_cutoff_hours' => ['required', 'integer', 'min:0', 'max:168'],
            'check_in_time_start' => ['required', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/'],
            'check_in_time_end' => ['required', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/'],
            'check_out_time' => ['required', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/'],
            'cancellation_policy' => ['required_if:is_published,true', 'nullable', 'string', 'max:2000'],
            'terms_text' => ['required_if:is_published,true', 'nullable', 'string', 'max:5000'],
            'is_published' => ['boolean'],
            'scta_business_name' => ['required_if:is_published,true', 'nullable', 'string', 'max:255'],
            'scta_representative' => ['required_if:is_published,true', 'nullable', 'string', 'max:255'],
            'scta_address' => ['required_if:is_published,true', 'nullable', 'string', 'max:255'],
            'scta_contact_email' => ['required_if:is_published,true', 'nullable', 'email', 'max:255'],
            'scta_contact_tel_disclaimer' => ['nullable', 'string', 'max:1000'],
            'scta_price_description' => ['nullable', 'string', 'max:1000'],
            'scta_payment_method_time' => ['nullable', 'string', 'max:1000'],
            'scta_service_delivery_time' => ['nullable', 'string', 'max:1000'],
            'slug' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9-]+$/',
                'unique:facilities,slug,' . ($this->user()->facilities()->first()->id ?? 'NULL'),
            ],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attributeは必須項目です。',
            'required_if' => '施設を公開するには、:attributeの入力が必須です。',
            'email' => '有効なメールアドレスを入力してください。',
            'max' => ':attributeは:max文字以内で入力してください。',
            'date_format' => ':attributeの形式が正しくありません。',
            'after' => ':attributeはチェックイン（開始）より後の時間を選択してください。',
            'regex' => ':attributeは英数字とハイフンのみ使用可能です。',
            'unique' => 'この:attributeは既に使用されています。',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '施設名',
            'address' => '所在地',
            'phone' => '電話番号',
            'booking_cutoff_hours' => '予約締め切り',
            'check_in_time_start' => 'チェックイン（開始）',
            'check_in_time_end' => 'チェックイン（終了）',
            'check_out_time' => 'チェックアウト',
            'cancellation_policy' => 'キャンセルポリシー',
            'terms_text' => '施設独自の規約 / 宿泊約款',
            'scta_business_name' => '運営事業者名',
            'scta_representative' => '運営責任者名',
            'scta_address' => '所在地（特商法）',
            'scta_contact_email' => 'お問い合わせ先メールアドレス',
            'slug' => '予約ページURL（カスタムID）',
            'image' => '施設画像1',
            'image_2' => '施設画像2',
            'image_3' => '施設画像3',
        ];
    }
}
