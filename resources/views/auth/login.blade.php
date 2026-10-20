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

        <div class="auth-field">
            <label for="password">
                Kata Sandi
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan kata sandi"
                autocomplete="current-password"
                required
            >
        </div>

        <!-- Teks Lupa Kata Sandi -->
        <div class="login-forgot">
            <span>Lupa kata sandi?</span>
        </div>

        <!-- Tombol Login -->
        <button type="submit" class="auth-button">
            Masuk
        </button>

    </form>

    <!-- Navigasi ke Register -->
    <p class="login-register">
        Belum punya akun?
        <a href="{{ route('register') }}">
            Daftar
        </a>
    </p>

</div>

@endsection
