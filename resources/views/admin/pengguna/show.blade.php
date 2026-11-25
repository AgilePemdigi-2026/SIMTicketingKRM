@extends('layouts.admin')

@section('title', 'Detail Pengguna')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-[#073B2D]">
                Detail Pengguna
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Informasi lengkap akun pengguna.
            </p>
        </div>

        <a href="{{ route('admin.pengguna.index', ['tab' => $user->role]) }}"
           class="rounded-lg border border-[#D7E3DA] px-4 py-2 text-sm font-semibold text-[#073B2D] hover:bg-[#F1F7F2]">
            Kembali
        </a>
    </div>

    {{-- DETAIL CARD --}}
    <div class="rounded-xl border border-[#E0E9E2] bg-white p-6 shadow-sm">
        <div class="mb-6 flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#E8F2EB] text-xl font-bold text-[#073B2D]">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>

            <div>
                <h2 class="text-lg font-bold text-[#073B2D]">
                    {{ $user->name }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ $user->email }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

            <div>
                <p class="text-sm text-gray-500">Nama Pengguna</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $user->name }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $user->email }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Nomor Telepon</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $user->phone ?: '-' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Nomor Identitas</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $user->identity_number ?: '-' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Role</p>
                <p class="mt-1 font-semibold capitalize text-gray-800">{{ $user->role }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Status Akun</p>
                <span class="mt-1 inline-block rounded-full px-3 py-1 text-xs font-semibold"
                      style="{{ $user->is_active ? 'background:#E4F4E8;color:#176B35;' : 'background:#FDE8E8;color:#B42318;' }}">
                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            <div>
                <p class="text-sm text-gray-500">Jenis Wisatawan</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $user->visitor_type ?: '-' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Tanggal Terdaftar</p>
                <p class="mt-1 font-semibold text-gray-800">
                    {{ $user->created_at?->format('d M Y, H:i') ?? '-' }}
                </p>
            </div>

        </div>
    </div>

</div>
@endsection
