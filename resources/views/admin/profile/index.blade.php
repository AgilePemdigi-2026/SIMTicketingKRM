@extends('layouts.admin')

@section('title', 'Profil Admin - Kebun Raya Mangrove')

@section('content')

@php
    $pageTitle = match($mode) {
        'edit' => 'Edit Profil',
        'password' => 'Ubah Password',
        default => 'Profil Admin',
    };

    $inputClass = 'mt-2 block w-full min-w-0 border border-[#D8E2DA] bg-white px-3 py-3 text-sm text-[#173D30] outline-none focus:border-[#527A65]';
@endphp

<div class="mx-auto max-w-[1100px]">

    <header class="mb-5 border-b border-[#B8C9BD] pb-4">

        <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#527A65]">
            Administrasi Kebun Raya Mangrove
        </p>

        <h1 class="mt-2 text-2xl font-medium text-[#0B372D]">
            {{ $pageTitle }}
        </h1>

        <p class="mt-2 text-xs text-[#74877B]">
            Kelola informasi pribadi dan keamanan akun administrator.
        </p>

    </header>

    @if(session('success'))
        <div role="status" class="mb-4 border border-[#CBD9CF] bg-[#E9F0EB] p-4 text-sm text-[#174B38]">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div role="alert" class="mb-4 border border-red-200 bg-red-50 p-4 text-xs text-red-700">
            <p class="mb-2 font-semibold">
                Periksa kembali informasi yang dimasukkan.
            </p>

            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mb-5 border border-[#D8E2DA] bg-white">

        <div class="flex flex-wrap items-center gap-4 p-5 sm:p-6">

            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[#E8F1EA] text-2xl font-semibold text-[#174B38]">

                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}

            </div>

            <div class="min-w-0 flex-1">

                <h2 class="break-words text-lg font-semibold text-[#173D30]">
                    {{ $user->name }}
                </h2>

                <p class="mt-1 break-all text-xs text-[#74877B]">
                    {{ $user->email }}
                </p>

                <p class="mt-2 text-xs font-medium text-[#527A65]">
                    Administrator
                </p>

            </div>

            @if($mode === 'show')
                <a href="{{ route('admin.profile.edit') }}"
                    class="bg-[#174B38] px-4 py-3 text-xs font-semibold text-white hover:bg-[#245744]">
                    Edit Profil
                </a>
            @endif

        </div>

        <div class="border-t border-[#D8E2DA] p-5 sm:p-6">

            @if($mode === 'show')

                <h2 class="mb-5 text-sm font-semibold">
                    Informasi Pribadi
                </h2>

                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    @foreach([
                        'Nama Lengkap' => $user->name,
                        'Email' => $user->email,
                        'Nomor Telepon' => $user->phone ?: '-',
                        'Role' => 'Administrator',
                    ] as $label => $value)

                        <div class="min-w-0">

                            <dt class="text-xs text-[#74877B]">
                                {{ $label }}
                            </dt>

                            <dd class="mt-2 break-words border border-[#D8E2DA] bg-[#F7F9F7] px-3 py-3 text-sm">
                                {{ $value }}
                            </dd>

                        </div>

                    @endforeach

                </dl>

            @elseif($mode === 'edit')

                <h2 class="mb-5 text-sm font-semibold">
                    Edit Informasi Pribadi
                </h2>

                <form method="POST" action="{{ route('admin.profile.update') }}">

                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div class="min-w-0">
                            <label for="name" class="text-xs font-medium">
                                Nama Lengkap
                            </label>

                            <input id="name" name="name"
                                type="text"
                                value="{{ old('name', $user->name) }}"
                                required maxlength="255"
                                autocomplete="name"
                                class="{{ $inputClass }}">
                        </div>

                        <div class="min-w-0">
                            <label for="email" class="text-xs font-medium">
                                Email
                            </label>

                            <input id="email" name="email"
                                type="email"
                                value="{{ old('email', $user->email) }}"
                                required maxlength="255"
                                autocomplete="email"
                                class="{{ $inputClass }}">
                        </div>

                        <div class="min-w-0">
                            <label for="phone" class="text-xs font-medium">
                                Nomor Telepon
                            </label>

                            <input id="phone" name="phone"
                                type="tel"
                                value="{{ old('phone', $user->phone) }}"
                                maxlength="25"
                                autocomplete="tel"
                                class="{{ $inputClass }}">
                        </div>

                        <div class="min-w-0">
                            <label for="profile-role" class="text-xs font-medium">
                                Role
                            </label>

                            <input id="profile-role"
                                type="text"
                                value="Administrator"
                                readonly
                                class="{{ $inputClass }} bg-[#F7F9F7]">

                            <p class="mt-2 text-[10px] text-[#74877B]">
                                Role tidak dapat diubah melalui halaman profil.
                            </p>
                        </div>

                    </div>

                    <div class="mt-6 flex flex-wrap justify-end gap-3">

                        <a href="{{ route('admin.profile.show') }}"
                            class="border border-[#D8E2DA] px-4 py-3 text-xs font-semibold hover:bg-[#F3F6F3]">
                            Batal
                        </a>

                        <button type="submit"
                            class="bg-[#174B38] px-4 py-3 text-xs font-semibold text-white hover:bg-[#245744]">
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            @else

                <h2 class="mb-5 text-sm font-semibold">
                    Keamanan Akun
                </h2>

                <form method="POST"
                    action="{{ route('admin.profile.password.update') }}"
                    class="max-w-[600px] space-y-5">

                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="text-xs font-medium">
                            Password Lama
                        </label>

                        <input id="current_password"
                            name="current_password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label for="password" class="text-xs font-medium">
                            Password Baru
                        </label>

                        <input id="password"
                            name="password"
                            type="password"
                            required minlength="8" maxlength="72"
                            autocomplete="new-password"
                            class="{{ $inputClass }}">

                        <p class="mt-2 text-[10px] text-[#74877B]">
                            Gunakan minimal 8 karakter dan berbeda dari password lama.
                        </p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-xs font-medium">
                            Konfirmasi Password Baru
                        </label>

                        <input id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="{{ $inputClass }}">
                    </div>

                    <div class="flex flex-wrap justify-end gap-3">

                        <a href="{{ route('admin.profile.show') }}"
                            class="border border-[#D8E2DA] px-4 py-3 text-xs font-semibold hover:bg-[#F3F6F3]">
                            Batal
                        </a>

                        <button type="submit"
                            class="bg-[#174B38] px-4 py-3 text-xs font-semibold text-white hover:bg-[#245744]">
                            Simpan Password
                        </button>

                    </div>

                </form>

            @endif

        </div>

    </section>

    @if($mode === 'show')

        <section class="flex flex-wrap items-center justify-between gap-4 border border-[#D8E2DA] bg-white p-5 sm:p-6">

            <div>
                <h2 class="text-sm font-semibold">
                    Keamanan Akun
                </h2>

                <p class="mt-2 text-xs text-[#74877B]">
                    Kelola kata sandi untuk menjaga keamanan akun administrator.
                </p>
            </div>

            <a href="{{ route('admin.profile.password.edit') }}"
                class="border border-[#174B38] px-4 py-3 text-xs font-semibold text-[#174B38] hover:bg-[#E9F0EB]">
                Ubah Password
            </a>

        </section>

    @endif

    <a href="{{ route('admin.dashboard') }}"
        class="mt-5 inline-flex items-center gap-2 text-xs font-semibold text-[#527A65] hover:underline">
        Kembali ke Dashboard
    </a>

</div>

@endsection
