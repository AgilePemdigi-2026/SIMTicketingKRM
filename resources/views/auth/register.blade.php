@extends('layouts.auth')

@section('title', 'Daftar Wisatawan')

@section('content')

<div class="register-content">

    <!-- Logo Daun -->
    <div class="register-logo">
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

    <h1>Daftar akun wisatawan</h1>

    <p class="register-subtitle">
        Buat akun untuk memesan tiket dan reservasi.
    </p>

    @if ($errors->any())
        <div class="auth-alert" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('register.store') }}"
          class="auth-form register-form">

        @csrf

        <div class="auth-field">
            <label for="name">Nama Lengkap</label>
            <input type="text"
                   id="name"
                   name="name"
                   value="{{ old('name') }}"
                   placeholder="Masukkan nama lengkap"
                   autocomplete="name"
                   required>
        </div>

        <div class="auth-field">
            <label for="identity_number">
                No. Identitas (KTP / Paspor)
            </label>
            <input type="text"
                   id="identity_number"
                   name="identity_number"
                   value="{{ old('identity_number') }}"
                   placeholder="Masukkan nomor identitas"
                   required>
        </div>

        <div class="auth-field">
            <label for="phone">Nomor Telepon</label>
            <input type="tel"
                   id="phone"
                   name="phone"
                   value="{{ old('phone') }}"
                   placeholder="Masukkan nomor telepon"
                   autocomplete="tel"
                   required>
        </div>

        <div class="auth-field">
            <label for="email">Email</label>
            <input type="email"
                   id="email"
                   name="email"
                   value="{{ old('email') }}"
                   placeholder="Masukkan alamat email"
                   autocomplete="email"
                   required>
        </div>

        <div class="auth-field">
            <label for="password">Kata Sandi</label>
            <input type="password"
                   id="password"
                   name="password"
                   placeholder="Minimal 8 karakter"
                   autocomplete="new-password"
                   required>
        </div>

        <div class="auth-field">
            <label for="password_confirmation">
                Konfirmasi Kata Sandi
            </label>
            <input type="password"
                   id="password_confirmation"
                   name="password_confirmation"
                   placeholder="Masukkan ulang kata sandi"
                   autocomplete="new-password"
                   required>
        </div>

        <div class="auth-field">
            <label for="visitor_type">
                Jenis Wisatawan
            </label>

            <select id="visitor_type"
                    name="visitor_type"
                    required>
                <option value="">
                    Pilih jenis wisatawan
                </option>

                <option value="domestik"
                    @selected(old('visitor_type') === 'domestik')>
                    Domestik (WNI)
                </option>

                <option value="mancanegara"
                    @selected(old('visitor_type') === 'mancanegara')>
                    Mancanegara (WNA)
                </option>
            </select>
        </div>

        <label class="register-terms">
            <input type="checkbox"
                   name="terms"
                   value="1"
                   @checked(old('terms'))
                   required>

            <span>
                Saya menyetujui syarat dan ketentuan penggunaan
            </span>
        </label>

        <button type="submit" class="auth-button">
            Daftar
        </button>

    </form>

    <p class="register-login">
        Sudah punya akun?
        <a href="{{ route('login') }}">Masuk</a>
    </p>

</div>

@endsection
