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
            border-bottom: 2px solid #e2e8f0;
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

        .info-title {
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 5px;
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

        .policy-box {
            border-left: 4px solid #ecc94b;
            background-color: #fffff0;
            padding: 15px;
            margin: 20px 0;
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
            <h1>ご予約が完了しました</h1>
        </div>

        <p>{{ $reservation->guest_name }} 様</p>
        <p>この度は、「{{ $facility->name }}」にご予約いただき、誠にありがとうございます。<br>
            @if($reservation->payment_method === 'onsite')
                以下の内容でご予約を承りました。
            @else
                クレジットカードでの決済が完了し、以下の内容で予約が確定いたしました。
            @endif
        </p>

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
                <span
                    class="value">{{ $reservation->check_in_date->format('Y年m月d日') }}（{{ substr($facility->check_in_time_start, 0, 5) }}〜{{ substr($facility->check_in_time_end, 0, 5) }}）</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">チェックアウト</span>
                <span
                    class="value">{{ $reservation->check_out_date->format('Y年m月d日') }}（{{ substr($facility->check_out_time, 0, 5) }}まで）</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">宿泊人数</span>
                <span class="value">{{ $reservation->number_of_guests }} 名様</span>
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
            @if($reservation->guest_remarks)
                <div style="margin-bottom: 8px; border-top: 1px solid #edf2f7; pt: 8px; mt: 8px;">
                    <div class="label" style="width: 100%; margin-bottom: 5px;">備考</div>
                    <div class="value" style="font-weight: normal; white-space: pre-wrap;">
                        {!! nl2br(htmlspecialchars($reservation->guest_remarks)) !!}
                    </div>
                </div>
            @endif
        </div>

        <div style="margin-bottom: 25px;">
            <div class="info-title">施設のご案内</div>
            <p style="margin: 5px 0;">所在地: {{ $facility->address }}</p>
        </div>

        <div class="policy-box">
            <strong>【キャンセルについて】</strong><br>
            {{ $facility->cancellation_policy ?: '詳細は施設へ直接お問い合わせください。' }}
        </div>

        <p>ご不明な点がございましたら、本メールへの返信にてお問い合わせください。<br>
            当日のご到着を心よりお待ち申し上げております。</p>

        <div class="footer">
            {{ config('app.name') }}<br>
            このメールはシステムより自動送信されています。
        </div>
    </div>
</body>

</html>