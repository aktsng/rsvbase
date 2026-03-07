<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if (app()->isProduction())
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-EBD1C0WD7T"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());

            gtag('config', 'G-EBD1C0WD7T');
        </script>
    @endif
    <meta name="description" content="RsvBase - 小規模宿泊施設向け直販予約システム">

    <title inertia>{{ config('app.name', 'RsvBase') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="/images/logo.ico">

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>