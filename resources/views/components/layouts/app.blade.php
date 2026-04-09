<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'TaskFlow') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    <x-nav />

    <main class="mx-auto max-w-5xl px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="mx-auto max-w-5xl px-4 py-8 text-sm text-gray-500">
        &copy; {{ date('Y') }} {{ config('app.name', 'TaskFlow') }}
    </footer>
</body>
</html>
