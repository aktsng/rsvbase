<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            border-bottom: 2px solid #3182ce;
            margin-bottom: 30px;
            padding-bottom: 10px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #718096;
        }

        .info-box {
            background-color: #f7fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .button {
            background-color: #3182ce;
            border-radius: 6px;
            color: #ffffff !important;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            padding: 12px 24px;
            text-decoration: none;
            margin: 20px 0;
        }

        .label {
            color: #718096;
            width: 120px;
            display: inline-block;
        }

        .value {
            font-weight: bold;
            color: #1a202c;
        }

        h1 {
            color: #1a202c;
            font-size: 24px;
            margin-bottom: 10px;
        }

        h2 {
            color: #2d3748;
            font-size: 18px;
            margin-top: 0;
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>新しい予約が入りました</h1>
        </div>

        <p>{{ $facility->name }} 様</p>
        @if($reservation->payment_method === 'onsite')
            <p>管理画面より新しい手動予約（現地決済）を登録しました。</p>
        @else
            <p>施設の新しい予約が確定しました。クレジットカードでの決済は完了しています。</p>
        @endif

        <div class="info-box">
            <h2>ご予約内容</h2>
            <div style="margin-bottom: 8px;">
                <span class="label">予約番号</span>
                <span class="value">{{ $reservation->reservation_code }}</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">お部屋</span>
                <span class="value">{{ $room->name }}</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">チェックイン</span>
                <span class="value">{{ $reservation->check_in_date->format('Y年m月d日') }}</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">チェックアウト</span>
                <span class="value">{{ $reservation->check_out_date->format('Y年m月d日') }}</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">宿泊人数</span>
                <span class="value">
                    @if($reservation->number_of_child_a > 0 || $reservation->number_of_child_b > 0)
                        大人 {{ $reservation->number_of_adults ?? $reservation->number_of_guests }} 名様
                        @if($reservation->number_of_child_a > 0) / {{ $room->child_a_label ?? '子供A' }}
                        {{ $reservation->number_of_child_a }} 名様@endif
                        @if($reservation->number_of_child_b > 0) / {{ $room->child_b_label ?? '子供B' }}
                        {{ $reservation->number_of_child_b }} 名様@endif
                    @else
                        {{ $reservation->number_of_guests }} 名様
                    @endif
                </span>
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">お支払い金額</span>
                <span class="value">
                    ¥{{ number_format($reservation->total_amount) }}
                    @if($reservation->payment_method === 'onsite')
                        （現地決済）
                    @else
                        （決済済）
                    @endif
                </span>
            </div>
        </div>

        <div class="info-box" style="background-color: #ebf8ff;">
            <h2>ゲスト情報</h2>
            <div style="margin-bottom: 8px;">
                <span class="label">お名前</span>
                <span class="value">{{ $reservation->guest_name }} 様</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">メールアドレス</span>
                <span class="value">{{ $reservation->guest_email }}</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">電話番号</span>
                <span class="value">{{ $reservation->guest_phone }}</span>
            </div>
            @if($reservation->guest_remarks)
                <div style="margin-bottom: 8px; border-top: 1px dotted #bee3f8; margin-top: 8px; padding-top: 8px;">
                    <div class="label" style="width: 100%; margin-bottom: 5px;">備考</div>
                    <div class="value" style="font-weight: normal; white-space: pre-wrap;">
                        {!! nl2br(htmlspecialchars($reservation->guest_remarks)) !!}
                    </div>
                </div>
            @endif
        </div>

        <div style="text-align: center;">
            <a href="{{ route('owner.reservations.index') }}" class="button">管理画面で確認する</a>
        </div>

        <div class="footer">
            {{ config('app.name') }}<br>
            このメールは管理用システムより自動送信されています。
        </div>
    </div>
</body>

</html>