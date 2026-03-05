<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メンテナンス中 | RsvBase</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e2e8f0;
            overflow: hidden;
            position: relative;
        }

        /* Animated background dots */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 20% 80%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(139, 92, 246, 0.05) 0%, transparent 50%);
            animation: bgPulse 8s ease-in-out infinite alternate;
        }

        @keyframes bgPulse {
            0% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 520px;
            padding: 2rem;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 0 40px rgba(99, 102, 241, 0.15);
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .icon-wrap {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.1);
            border: 2px solid rgba(99, 102, 241, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: iconBreathe 3s ease-in-out infinite;
        }

        @keyframes iconBreathe {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.1);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 0 20px 5px rgba(99, 102, 241, 0.08);
            }
        }

        .icon-wrap svg {
            width: 36px;
            height: 36px;
            color: #818cf8;
        }

        .status-code {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #818cf8;
            margin-bottom: 1rem;
            background: rgba(99, 102, 241, 0.1);
            display: inline-block;
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #f1f5f9;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
            line-height: 1.3;
        }

        .message {
            font-size: 0.95rem;
            color: #94a3b8;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .progress-bar {
            width: 200px;
            height: 3px;
            background: rgba(99, 102, 241, 0.15);
            border-radius: 999px;
            margin: 0 auto 2rem;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            display: block;
            width: 40%;
            height: 100%;
            background: linear-gradient(90deg, #6366f1, #818cf8);
            border-radius: 999px;
            animation: progressSlide 2s ease-in-out infinite;
        }

        @keyframes progressSlide {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(350%);
            }
        }

        .footer {
            font-size: 0.75rem;
            color: #475569;
            margin-top: 1rem;
        }

        .footer a {
            color: #6366f1;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="/images/logo.png" alt="RsvBase">
        </div>

        <div class="icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>

        <div class="status-code">503 Service Unavailable</div>

        <h1>ただいまメンテナンス中です</h1>

        <p class="message">
            サービスの品質向上のため、現在メンテナンスを実施しております。<br>
            ご不便をおかけしますが、しばらくお待ちください。
        </p>

        <div class="progress-bar"></div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} RsvBase — 宿泊予約管理プラットフォーム</p>
        </div>
    </div>
</body>

</html>