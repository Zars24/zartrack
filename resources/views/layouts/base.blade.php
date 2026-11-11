<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'ZarTrack — Container trucking, freight forwarding, warehousing, dan logistik terintegrasi.')">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#0a0f18">
    <title>@yield('title', 'ZarTrack') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:500,600,700,800|instrument-sans:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet">
    <script>
        (function () {
            document.documentElement.dataset.theme = localStorage.getItem('zt-theme') === 'dark' ? 'dark' : 'light';
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
@yield('body')
@stack('scripts')
</body>
</html>