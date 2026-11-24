@extends('layouts.auth')

@section('title', 'Login Wisatawan')

@section('content')

<div class="login-content">

    <!-- Logo Daun -->
    <div class="login-logo">
        <svg width="32" height="32"
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

    <!-- Judul Login -->
    <h1>Selamat datang kembali</h1>

    <p class="login-subtitle">
        Masuk untuk mengelola tiket dan reservasi Anda.
    </p>

    <!-- Pesan Berhasil -->
    @if (session('success'))
        <div class="auth-alert success" role="status">
            {{ session('success') }}
        </div>
    @endif

    <!-- Pesan Validasi -->
    @if ($errors->any())
        <div class="auth-alert" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Login -->
    <form method="POST"
          action="{{ route('login.store') }}"
          class="auth-form">

        @csrf

        <!-- Email / Nomor Telepon -->
        <div class="auth-field">
            <label for="login">
                Email / No. Telepon
            </label>

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

        <!-- Kata Sandi -->
        <div class="auth-field">
            <label for="password">
                Kata Sandi
            </label>

            <div class="password-wrapper">
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan kata sandi"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="button"
                    id="togglePassword"
                    class="password-toggle"
                    aria-label="Tampilkan kata sandi"
                    aria-controls="password"
                    aria-pressed="false"
                >
                    <!-- Mata terbuka -->
                    <svg id="eyeOpen" width="22" height="22"
                        viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>

                    <!-- Mata dicoret -->
                    <svg id="eyeClosed" width="22" height="22"
                        viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round"
                        style="display:none;">
                        <path d="M3 3l18 18"/>
                        <path d="M10.6 5.1A10.5 10.5 0 0 1 12 5c6.5 0 10 7 10 7a14 14 0 0 1-3.1 4.2"/>
                        <path d="M6.1 6.1A14 14 0 0 0 2 12s3.5 7 10 7a10.5 10.5 0 0 0 5.9-1.8"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Tombol Login -->
        <button type="submit" class="auth-button">
            Masuk
        </button>

    </form>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');
            const show = input.type === 'password';

            input.type = show ? 'text' : 'password';
            eyeOpen.style.display = show ? 'none' : 'block';
            eyeClosed.style.display = show ? 'block' : 'none';

            this.setAttribute('aria-label',
                show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            this.setAttribute('aria-pressed', String(show));
        });
    </script>

    <!-- Navigasi ke Register -->
    <p class="login-register">
        Belum punya akun?
        <a href="{{ route('register') }}">
            Daftar
        </a>
    </p>

</div>

@endsection
