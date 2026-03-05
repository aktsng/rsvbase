<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'base_price_per_night' => ['required', 'integer', 'min:0'],
            'weekend_price_per_night' => ['nullable', 'integer', 'min:0'],
            'cleaning_fee' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'image1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            // 料金タイプ・定員
            'pricing_type' => ['required', 'in:room,person'],
            'min_guests' => ['required', 'integer', 'min:1', 'max:20'],
        ];

        // 人数単価の場合のみ追加料金・子供設定を検証
        if ($this->input('pricing_type') === 'person') {
            $rules = array_merge($rules, [
                // 子供設定（トグルがOFFなら送信されないのでnullable）
                'child_a_label' => ['nullable', 'string', 'max:100'],
                'child_a_policy' => ['nullable', 'string', 'max:255'],
                'child_a_is_counted' => ['nullable', 'boolean'],
                'add_child_a_fee' => ['nullable', 'integer', 'min:0'],
                'child_b_label' => ['nullable', 'string', 'max:100'],
                'child_b_policy' => ['nullable', 'string', 'max:255'],
                'child_b_is_counted' => ['nullable', 'boolean'],
                'add_child_b_fee' => ['nullable', 'integer', 'min:0'],
            ]);
        }

        return $rules;
    }
}
