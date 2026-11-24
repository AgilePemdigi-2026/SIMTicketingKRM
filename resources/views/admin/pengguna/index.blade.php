@extends('layouts.admin')

@section('title', 'Manajemen Pengguna - Admin')

@section('content')
<div class="space-y-6 text-[#29483D]">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <div class="mb-2 flex items-center gap-2 text-[10px] text-[#84968B]">
                            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
                Administrasi Kebun Raya Mangrove
            </p>
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-[#073B2D]">
                Manajemen Pengguna
            </h1>

            <p class="mt-1.5 text-xs text-[#829188]">
                Kelola akun wisatawan dan petugas Kebun Raya Mangrove.
            </p>
        </div>
    </div>

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-[#CDE5D2] bg-[#F0F8F1] px-4 py-3 text-xs text-[#287044]">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#DCEFE0] font-bold">✓</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- FILTER PENGGUNA --}}
    <div class="rounded-xl border border-[#E0E9E2] bg-white p-5 shadow-sm">

        <form method="GET" action="{{ route('admin.pengguna.index') }}"
              class="grid grid-cols-1 gap-4 md:grid-cols-3">

            <input type="hidden" name="tab" value="{{ $tab }}">

            <div>
                <label class="mb-2 block text-[10px] font-semibold text-[#526D5E]">
                    Cari Pengguna
                </label>

                <div class="relative">

                    <input
                       class="h-10 w-full rounded-lg border border-[#DCE6DF] bg-[#FAFCFA] px-3 text-[11px] outline-none transition focus:border-[#6D9C7B] focus:bg-white">
                </div>
            </div>

            <div>
                <label class="mb-2 block text-[10px] font-semibold text-[#526D5E]">
                    Status Akun
                </label>

                <select name="status"
                        class="h-10 w-full rounded-lg border border-[#DCE6DF] bg-[#FAFCFA] px-3 text-[11px] outline-none transition focus:border-[#6D9C7B] focus:ring-2 focus:ring-[#6D9C7B]/10">
                    <option value="">Semua Status</option>
                    <option value="1" @selected(request('status') === '1')>Aktif</option>
                    <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-[10px] font-semibold text-[#526D5E]">
                    Aktivitas Terakhir
                </label>

                <input
                    type="date"
                    name="activity_date"
                    value="{{ request('activity_date') }}"
                    class="h-10 w-full rounded-lg border border-[#DCE6DF] bg-[#FAFCFA] px-3 text-[11px] outline-none transition focus:border-[#6D9C7B] focus:ring-2 focus:ring-[#6D9C7B]/10"
                >
            </div>

            <div class="flex items-center justify-start gap-3 md:col-span-3">
    <button
        type="submit"
        style="background-color: #0B4936; color: #FFFFFF;"
        class="rounded-lg px-5 py-2.5 text-[11px] font-semibold transition hover:opacity-90"
    >
        Terapkan Filter
    </button>

    <a
        href="{{ route('admin.pengguna.index', ['tab' => $tab]) }}"
        style="background-color: #A63D40; color: #FFFFFF;"
        class="rounded-lg px-5 py-2.5 text-[11px] font-semibold transition hover:opacity-90"
    >
        Reset
    </a>
</div>

        </form>
    </div>

 {{-- DAFTAR PENGGUNA --}}
<div class="space-y-4">

    {{-- JUDUL DAN TAB --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <h3 class="text-sm font-bold text-[#173F30]">
            Daftar Pengguna
        </h3>

        <div class="flex items-center gap-2">

            {{-- TAB WISATAWAN --}}
            <a
                href="{{ route('admin.pengguna.index', array_merge(request()->query(), ['tab' => 'wisatawan'])) }}"
                style="{{ $tab === 'wisatawan'
                    ? 'background-color: #0B4936; color: #FFFFFF;'
                    : 'background-color: #FFFFFF; color: #829188; border: 1px solid #DCE6DF;' }}"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-[11px] font-semibold transition"
            >
                Wisatawan

                <span
                    style="{{ $tab === 'wisatawan'
                        ? 'background-color: rgba(255,255,255,0.15); color: #FFFFFF;'
                        : 'background-color: #EAF0EB; color: #718078;' }}"
                    class="rounded-md px-1.5 py-0.5 text-[9px]"
                >
                    {{ $jumlahWisatawan }}
                </span>
            </a>

            {{-- TAB PETUGAS --}}
            <a
                href="{{ route('admin.pengguna.index', array_merge(request()->query(), ['tab' => 'petugas'])) }}"
                style="{{ $tab === 'petugas'
                    ? 'background-color: #0B4936; color: #FFFFFF;'
                    : 'background-color: #FFFFFF; color: #829188; border: 1px solid #DCE6DF;' }}"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-[11px] font-semibold transition"
            >
                Petugas

                <span
                    style="{{ $tab === 'petugas'
                        ? 'background-color: rgba(255,255,255,0.15); color: #FFFFFF;'
                        : 'background-color: #EAF0EB; color: #718078;' }}"
                    class="rounded-md px-1.5 py-0.5 text-[9px]"
                >
                    {{ $jumlahPetugas }}
                </span>
            </a>

        </div>
    </div>

    {{-- TABEL --}}
    <div class="overflow-hidden rounded-xl border border-[#DCE5DF] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] border-collapse text-left">

                <thead style="background-color: #E5F0E7;">
                    <tr class="text-[9px] font-bold uppercase tracking-[0.08em] text-[#718579]">
                        <th class="px-5 py-4">Nama Pengguna</th>
                        <th class="px-4 py-4">Email</th>
                        <th class="px-4 py-4">No. Telepon</th>
                        <th class="px-4 py-4">Role</th>
                        <th class="px-4 py-4">Status</th>
                        <th class="px-4 py-4">Aktivitas Terakhir</th>
                        <th class="px-4 py-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#EDF1ED]">

                    @forelse ($pengguna as $user)
                        <tr class="group text-[10px] transition hover:bg-[#F9FBF9]">

                            {{-- NAMA --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#E8F1E9] text-[11px] font-bold text-[#28684C]">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="max-w-[180px] truncate font-bold text-[#254A38]">
                                            {{ $user->name }}
                                        </p>
                                        <p class="mt-1 text-[9px] text-[#9AA79F]">
                                            ID #{{ $user->id }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- EMAIL --}}
                            <td class="px-4 py-4 text-[#61766A]">
                                {{ $user->email }}
                            </td>

                            {{-- TELEPON --}}
                            <td class="px-4 py-4 text-[#61766A]">
                                {{ $user->phone ?? '-' }}
                            </td>

                            {{-- ROLE --}}
                            <td class="px-4 py-4">
                                <span class="rounded-md bg-[#EEF3EF] px-2.5 py-1 text-[9px] font-semibold text-[#496B56]">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            {{-- STATUS --}}
                            <td class="px-4 py-4">
                                @if ($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#E8F4E9] px-2.5 py-1 text-[9px] font-semibold text-[#287044]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#399653]"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#F0F1EF] px-2.5 py-1 text-[9px] font-semibold text-[#778078]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#929B93]"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- AKTIVITAS --}}
                            <td class="px-4 py-4 text-[#718579]">
                                @if ($user->last_activity)
                                    {{ date('d M Y', $user->last_activity) }}
                                @else
                                    <span class="text-[#A2ADA5]">Belum ada aktivitas</span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-1.5 whitespace-nowrap">
<a href="{{ route('admin.pengguna.show', $user->id) }}"
   class="text-sm font-semibold text-[#176B45] hover:underline">
    Lihat
</a>

<form action="{{ route('admin.pengguna.update-role', $user->id) }}"
      method="POST"
      class="inline">
    @csrf
    @method('PATCH')

    <select name="role"
            onchange="this.form.submit()"
            class="rounded-md border border-[#DCE6DF] px-2 py-1.5 text-[9px] font-semibold text-[#496B56] focus:outline-none">

        <option value="wisatawan" {{ $user->role === 'wisatawan' ? 'selected' : '' }}>
            Wisatawan
        </option>

        <option value="petugas" {{ $user->role === 'petugas' ? 'selected' : '' }}>
            Petugas
        </option>

        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
            Admin
        </option>
    </select>
</form>

                                    <form action="{{ route('admin.pengguna.update-status', $user->id) }}"
      method="POST"
      class="inline"
      onsubmit="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun ini?')">
    @csrf
    @method('PATCH')

    @if ($user->is_active)
        <button type="submit"
                class="rounded-md border border-[#EBDCC5] bg-[#FFFAF1] px-2 py-1.5 text-[9px] font-semibold text-[#A36D28] hover:bg-[#FFF2D9]">
            Nonaktifkan
        </button>
    @else
        <button type="submit"
                class="rounded-md border border-[#DCE6DF] bg-white px-2 py-1.5 text-[9px] font-semibold text-[#496B56] hover:bg-[#F1F7F2]">
            Aktifkan
        </button>
    @endif
</form>
                                </div>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#F0F5F0] text-[#829B89]">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                        <circle cx="11" cy="11" r="7"/>
                                        <path d="m20 20-4-4"/>
                                    </svg>
                                </div>

                                <p class="mt-3 text-xs font-bold text-[#496457]">
                                    Pengguna tidak ditemukan
                                </p>

                                <p class="mt-1 text-[10px] text-[#9AA79F]">
                                    Tidak ada data {{ $tab }} yang sesuai dengan filter.
                                </p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

{{-- PAGINATION --}}
@if ($pengguna->hasPages())
    <div class="flex flex-col justify-between gap-3 border-t border-[#E8EEE9] px-5 py-4 sm:flex-row sm:items-center">
        {{ $pengguna->links() }}
    </div>
@endif

    </div>

</div>
@endsection
