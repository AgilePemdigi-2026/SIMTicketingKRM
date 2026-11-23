@php
    $isAdmin = ($portalRole ?? '') === 'admin';

    $portalLabel = $isAdmin
        ? 'KRM ADMIN'
        : 'KRM PETUGAS';

    $portalTitle = $isAdmin
        ? 'Portal Administrasi'
        : 'Portal Operasional';

    $portalFooter = $isAdmin
        ? 'Administrator Kebun Raya Mangrove · Portal Administrasi'
        : 'Petugas Kebun Raya Mangrove · Portal Operasional';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $portalTitle }} - SIMTicketingKRM</title>

    <link rel="stylesheet"
          href="{{ asset('css/auth.css') }}?v=3">
</head>

<body class="portal-page">

    <main class="portal-wrapper">

        <section class="portal-card">

            <!-- Logo daun -->
            <div class="portal-logo">
                <svg width="34" height="34"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="#267658"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M20 4c-9 0-15 4-15 12a4 4 0 0 0 4 4c8 0 12-6 11-16Z"/>
                    <path d="M5 20c3-6 7-9 12-12"/>
                </svg>
            </div>

            <div class="portal-heading">
                <span class="portal-label">
                    {{ $portalLabel }}
                </span>

                <h1>{{ $portalTitle }}</h1>

                <p>Kebun Raya Mangrove Surabaya</p>
            </div>

            @if (session('success'))
                <div class="auth-alert success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="auth-alert" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form tetap terhubung ke LoginController -->
            <form method="POST"
                  action="{{ route('login.store') }}"
                  class="portal-form">

                @csrf

                <div class="portal-field">
                    <label for="login">Email/No Telepon</label>

                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="{{ old('login') }}"
                        placeholder="Masukkan email atau nomor telepon"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="portal-field">
                    <label for="password">Password</label>

                    <div class="portal-password">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="portal-password-toggle"
                            data-toggle-password
                            aria-label="Tampilkan password"
                            aria-controls="password"
                            aria-pressed="false"
                        >
                            <!-- Ikon mata terbuka -->
                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg"
                                width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round"
                                aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>

                            <!-- Ikon mata dicoret -->
                            <svg class="eye-close" xmlns="http://www.w3.org/2000/svg"
                                width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round"
                                style="display:none;"
                                aria-hidden="true">
                                <path d="M10.6 5.2A11 11 0 0 1 12 5c6.5 0 10 7 10 7a15 15 0 0 1-3.2 4.1"/>
                                <path d="M6.4 6.5C3.5 8.5 2 12 2 12s3.5 7 10 7a10 10 0 0 0 5.3-1.5"/>
                                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                                <path d="M3 3l18 18"/>
                            </svg>

                        </button>
                    </div>
                </div>

                <button type="submit" class="portal-button">
                    MASUK
                </button>

            </form>

            <div class="portal-footer">
                {{ $portalFooter }}
            </div>

        </section>

    </main>

    <script>
        (() => {
            const toggle = document.querySelector('[data-toggle-password]');
            const passwordInput = document.getElementById('password');

            if (!toggle || !passwordInput) return;

            const eyeOpen = toggle.querySelector('.eye-open');
            const eyeClose = toggle.querySelector('.eye-close');

            toggle.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';

                passwordInput.type = isHidden ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                toggle.setAttribute(
                    'aria-label',
                    isHidden ? 'Sembunyikan password' : 'Tampilkan password'
                );

                if (eyeOpen && eyeClose) {
                    eyeOpen.style.display = isHidden ? 'none' : 'inline';
                    eyeClose.style.display = isHidden ? 'inline' : 'none';
                }
            });
        })();
    </script>

</body>
</html>
