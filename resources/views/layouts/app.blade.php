<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Kebun Raya Mangrove Surabaya')</title>

    {{-- Tailwind CSS dan JavaScript bawaan project Laravel --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="flex min-h-screen flex-col bg-[#FBFCF8] font-sans text-[#193B31] antialiased">
    <a href="#main-content"
       class="sr-only rounded bg-white px-4 py-2 text-[#063A2B] focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100]">
        Lewati ke konten
    </a>

    @include('partials.navbar')

    <main id="main-content" class="w-full flex-1">
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>