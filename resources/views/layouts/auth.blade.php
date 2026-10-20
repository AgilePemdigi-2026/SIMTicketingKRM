<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - SIMTicketingKRM</title>

    <link rel="stylesheet"
          href="{{ asset('css/auth.css') }}?v=2">
</head>

<body class="auth-page">

    <main class="auth-wrapper">

        <!-- BAGIAN KIRI: FOTO MANGROVE -->
        <section class="auth-image">

            <div class="auth-image-content">

                <div class="auth-brand">

                    <div class="auth-brand-icon">
                        <svg width="29" height="29"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.7"
                             stroke-linecap="round"
                             stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M20 4c-9 0-15 4-15 12a4 4 0 0 0 4 4c8 0 12-6 11-16Z"/>
                            <path d="M5 20c3-6 7-9 12-12"/>
                        </svg>
                    </div>

                    <div class="auth-brand-text">
                        <strong>KEBUN RAYA</strong>
                        <span>MANGROVE SURABAYA</span>
                    </div>

                </div>

                <div class="auth-image-bottom">
                    <span>WISATA ALAM SURABAYA</span>

                    <h2>
                        Alam lebih dekat,<br>
                        kunjungan lebih mudah.
                    </h2>
                </div>

            </div>
        </section>

        <!-- BAGIAN KANAN: FORM -->
        <section class="auth-panel">
            <div class="auth-container">
                @yield('content')
            </div>
        </section>

    </main>

</body>
</html>
