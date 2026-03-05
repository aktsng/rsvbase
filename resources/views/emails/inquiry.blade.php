<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .header {
            font-weight: bold;
            font-size: 1.2em;
            color: #0d9488;
            margin-bottom: 20px;
            border-bottom: 2px solid #f0fdfa;
            padding-bottom: 10px;
        }

        .field {
            margin-bottom: 15px;
        }

        .label {
            font-weight: bold;
            color: #64748b;
            font-size: 0.9em;
            margin-bottom: 4px;
        }

        .content {
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">RsvBase ホームページからのお問い合わせ</div>

        <div class="field">
            <div class="label">お名前</div>
            <div class="content">{{ $data['name'] }}</div>
        </div>

        <div class="field">
            <div class="label">メールアドレス</div>
            <div class="content">{{ $data['email'] }}</div>
        </div>

        <div class="field">
            <div class="label">施設名（屋号）</div>
            <div class="content">{{ $data['facility_name'] ?? '未入力' }}</div>
        </div>

        <div class="field">
            <div class="label">客室数</div>
            <div class="content">{{ $data['rooms_count'] ?? '未選択' }}</div>
        </div>

        <div class="field">
            <div class="label">お問い合わせ内容</div>
            <div class="content">{{ $data['message'] }}</div>
        </div>

        <div style="margin-top: 30px; font-size: 0.8em; color: #94a3b8; text-align: center;">
            &copy; 2026 RsvBase
        </div>
    </div>
</body>

</html>