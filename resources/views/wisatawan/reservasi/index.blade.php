@extends('layouts.app')

@section('title', 'Reservasi - Pilih Fasilitas')

@section('content')
<div class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:px-8">

    {{-- Progress Reservasi --}}
    <div class="mx-auto mb-14 max-w-3xl">
        @php
            $langkahReservasi = [
                'Pilih Fasilitas',
                'Pilih Tanggal',
                'Data Reservasi',
                'Pembayaran',
                'Invoice',
            ];
        @endphp

        <div class="relative flex items-start justify-between">
            <div class="absolute left-[10%] right-[10%] top-4 h-px bg-gray-200"></div>

            @foreach ($langkahReservasi as $index => $langkah)
                <div class="relative z-10 flex flex-1 flex-col items-center gap-2 text-center">
                    <div class="flex h-8 w-8 items-center justify-center border text-sm font-semibold
                        {{ $index === 0
                            ? 'border-[#07513D] bg-[#07513D] text-white'
                            : 'border-gray-200 bg-[#FBFCF8] text-gray-400' }}"
                        @if($index === 0) aria-current="step" @endif>
                        {{ $index + 1 }}
                    </div>

                    <span class="text-[10px] sm:text-xs
                        {{ $index === 0
                            ? 'font-semibold text-[#07513D]'
                            : 'text-gray-400' }}">
                        {{ $langkah }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Judul Halaman --}}
    <div class="mb-8">
        <h1 class="krm-page-title">
            Pilih Fasilitas
        </h1>
    </div>

    @php
        $fasilitasTerpilih = $fasilitas->firstWhere(
            'id_fasilitas',
            (int) request('fasilitas')
        );
    @endphp

    @if ($fasilitasTerpilih)
        <div role="status" class="mb-6 border border-[#C9DDCF] bg-[#F0F7F0] p-4 text-sm text-[#07513D]">
            <strong>{{ $fasilitasTerpilih->nama_fasilitas }}</strong>
            sudah dipilih. Tahap pemilihan jadwal akan kita hubungkan berikutnya.
        </div>
    @endif

    {{-- Daftar Fasilitas --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

        @forelse ($fasilitas as $item)
            @php
                $foto = $item->foto_fasilitas;

                $urlFoto = $foto
                    ? (
                        \Illuminate\Support\Str::startsWith(
                            $foto,
                            ['http://', 'https://']
                        )
                            ? $foto
                            : asset('storage/' . ltrim($foto, '/'))
                    )
                    : asset('images/destinasi/boardwalk-hero.jpg');

                $hargaMulai = $item->sesiFasilitas
                    ->min('harga_sesi');

                $dipilih = $fasilitasTerpilih
                    && $fasilitasTerpilih->id_fasilitas === $item->id_fasilitas;
            @endphp

            <article class="flex h-full flex-col overflow-hidden border border-gray-200 bg-white shadow-sm">

                {{-- Foto Fasilitas --}}
                <img
                    src="{{ $urlFoto }}"
                    alt="{{ $item->nama_fasilitas }}"
                    class="h-44 w-full object-cover"
                    loading="lazy"
                >

                {{-- Informasi Fasilitas --}}
                <div class="flex flex-1 flex-col p-4">

                    <h2 class="mb-2 text-base font-bold text-[#193B31]">
                        {{ $item->nama_fasilitas }}
                    </h2>

                    <p class="mb-2 text-xs text-gray-500">
                        Kapasitas:
                        {{ number_format($item->kapasitas_fasilitas, 0, ',', '.') }}
                        orang
                    </p>

                    <p class="mb-2 text-sm font-bold text-[#193B31]">
                        Mulai Rp
                        {{ number_format((float) $hargaMulai, 0, ',', '.') }}
                        / sesi
                    </p>

                    <p class="mb-5 flex-1 text-xs leading-relaxed text-gray-500">
                        {{ \Illuminate\Support\Str::limit(
                            $item->deskripsi_fasilitas
                                ?: 'Fasilitas tersedia untuk kebutuhan kegiatan dan reservasi.',
                            120
                        ) }}
                    </p>

                    {{-- Pilih Fasilitas --}}
                    <a
                        href="{{ route('wisatawan.reservasi.tanggal', $item->id_fasilitas) }}"
                        class="block w-full bg-[#07513D] px-4 py-3 text-center text-sm font-semibold text-white transition hover:bg-[#063A2B] focus:outline-none focus:ring-2 focus:ring-[#07513D] focus:ring-offset-2"
                    >
                        {{ $dipilih ? 'Fasilitas Terpilih' : 'Pilih Fasilitas' }}
                    </a>

                </div>
            </article>

        @empty
            <div class="col-span-full border border-gray-200 bg-white px-6 py-12 text-center">
                <h2 class="mb-2 font-semibold text-[#193B31]">
                    Belum Ada Fasilitas Tersedia
                </h2>

                <p class="text-sm text-gray-500">
                    Saat ini belum ada fasilitas aktif dengan sesi penyewaan yang tersedia.
                </p>
            </div>
        @endforelse

    </div>
</div>
@endsection
