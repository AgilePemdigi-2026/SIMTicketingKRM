@php
    $kategoriAktif = $kategoriAktif ?? '';

    $menuKonten = [
        [
            'key' => 'destinasi',
            'label' => 'Info Destinasi',
            'route' => 'admin.konten-destinasi.index',
        ],
        [
            'key' => 'galeri',
            'label' => 'Galeri',
            'route' => 'admin.konten-galeri.index',
        ],
        [
            'key' => 'berita',
            'label' => 'Berita & Artikel',
            'route' => 'admin.konten-berita.index',
        ],
        [
            'key' => 'kontak',
            'label' => 'Info Kontak',
            'route' => 'admin.konten-kontak.index',
        ],
    ];
@endphp

{{-- NAVIGASI KATEGORI MANAJEMEN KONTEN --}}
<aside class="w-full shrink-0 self-start border border-[#DCE5DE] bg-white p-3 lg:w-[205px]">

    <div class="border-b border-[#E2E9E4] px-2 py-3">
        <h2 class="text-[11px] font-bold uppercase tracking-[0.08em] text-[#173D30]">
            Kategori Konten
        </h2>
    </div>

    <nav aria-label="Kategori Manajemen Konten"
         class="mt-2 space-y-1">

        @foreach ($menuKonten as $menu)

            @php
                $aktif = $kategoriAktif === $menu['key'];

                $tersedia = $menu['route']
                    && \Illuminate\Support\Facades\Route::has($menu['route']);
            @endphp

            @if ($tersedia)
                <a
                    href="{{ route($menu['route']) }}"
                    @if ($aktif) aria-current="page" @endif
                    class="flex items-center justify-between px-3 py-3 text-xs transition
                        {{ $aktif
                            ? 'bg-[#064631] font-semibold text-white'
                            : 'text-[#50695B] hover:bg-[#E9F0EB]' }}"
                >
                    <span>{{ $menu['label'] }}</span>
                    <span aria-hidden="true">›</span>
                </a>
            @else
                <div
                    class="flex items-center justify-between px-3 py-3 text-xs text-[#A0ACA4]"
                    title="Modul belum tersedia"
                >
                    <span>{{ $menu['label'] }}</span>
                    <span aria-hidden="true">›</span>
                </div>
            @endif

        @endforeach

    </nav>

</aside>
