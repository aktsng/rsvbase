ご予約が完了しました

{{ $reservation->guest_name }} 様

この度は、「{{ $facility->name }}」にご予約いただき、誠にありがとうございます。
@if($reservation->payment_method === 'onsite')
    以下の内容でご予約を承りました。
@else
    クレジットカードでの決済が完了し、以下の内容で予約が確定いたしました。
@endif

■ご予約内容
予約番号：{{ $reservation->reservation_code }}
お部屋：{{ $room->name }}
チェックイン：{{ $reservation->check_in_date->format('Y年m月d日') }}（{{ substr($facility->check_in_time_start, 0, 5) }}〜{{ substr($facility->check_in_time_end, 0, 5) }}）
チェックアウト：{{ $reservation->check_out_date->format('Y年m月d日') }}（{{ substr($facility->check_out_time, 0, 5) }}まで）
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

@if($reservation->guest_remarks)■備考
{{ $reservation->guest_remarks }}@endif

■施設のご案内
所在地：{{ $facility->address }}

■キャンセルについて
{{ $facility->cancellation_policy ?: '詳細は施設へ直接お問い合わせください。' }}

ご不明な点がございましたら、本メールへの返信にてお問い合わせください。
当日のご到着を心よりお待ち申し上げております。

--------------------------------------------------
{{ config('app.name') }}
このメールはシステムより自動送信されています。