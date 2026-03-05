新しい予約が入りました

{{ $facility->name }} 様

@if($reservation->payment_method === 'onsite')
    管理画面より新しい手動予約（現地決済）を登録しました。
@else
    施設の新しい予約が確定しました。クレジットカードでの決済は完了しています。
@endif

■ご予約内容
予約番号：{{ $reservation->reservation_code }}
お部屋：{{ $room->name }}
チェックイン：{{ $reservation->check_in_date->format('Y年m月d日') }}
チェックアウト：{{ $reservation->check_out_date->format('Y年m月d日') }}
@if($reservation->number_of_child_a > 0 || $reservation->number_of_child_b > 0)
    宿泊人数：大人 {{ $reservation->number_of_adults ?? $reservation->number_of_guests }}
    名様@if($reservation->number_of_child_a > 0) / {{ $room->child_a_label ?? '子供A' }} {{ $reservation->number_of_child_a }}
    名様@endif @if($reservation->number_of_child_b > 0) / {{ $room->child_b_label ?? '子供B' }}
    {{ $reservation->number_of_child_b }} 名様@endif

@else
    宿泊人数：{{ $reservation->number_of_guests }} 名様
@endif
お支払い金額：¥{{ number_format($reservation->total_amount) }}
@if($reservation->payment_method === 'onsite')（現地決済）@else（決済済）@endif

■ゲスト情報
お名前：{{ $reservation->guest_name }} 様
メールアドレス：{{ $reservation->guest_email }}
電話番号：{{ $reservation->guest_phone }}
@if($reservation->guest_remarks)■備考
{{ $reservation->guest_remarks }}@endif

管理画面で確認する：
{{ route('owner.reservations.index') }}

--------------------------------------------------
{{ config('app.name') }}
このメールは管理用システムより自動送信されています。