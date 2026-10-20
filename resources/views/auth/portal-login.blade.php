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

                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
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

</body>
</html>
