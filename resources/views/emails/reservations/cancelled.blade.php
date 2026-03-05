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
            width: 140px;
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
            <h1>ご予約のキャンセル完了のお知らせ</h1>
        </div>

        <p>{{ $reservation->guest_name }} 様</p>
        <p>「{{ $facility->name }}」でございます。<br>
            以下のご予約につきまして、キャンセル処理が完了いたしましたのでお知らせいたします。</p>

        <div class="info-box">
            <h2>キャンセルされたご予約</h2>
            <div style="margin-bottom: 8px;">
                <span class="label">予約番号</span>
                <span class="value">{{ $reservation->reservation_code }}</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">お部屋</span>
                <span class="value">{{ $room->name }}</span>
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">チェックイン予定日</span>
                <span class="value">{{ $reservation->check_in_date->format('Y年m月d日') }}</span>
            </div>
        </div>

        @if($reservation->payment_method !== 'onsite')
            <p>本件につきましては、キャンセルポリシーに基づき、ご返金はございません。<br>
                何卒ご了承くださいますようお願い申し上げます。</p>
        @endif

        <p>ご不明な点がございましたら、施設までお問い合わせください。</p>

        <p>またのご利用を心よりお待ち申し上げております。</p>

        <div class="footer">
            {{ config('app.name') }}<br>
            このメールはシステムより自動送信されています。
        </div>
    </div>
</body>

</html>