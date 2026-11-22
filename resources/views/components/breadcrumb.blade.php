@props(['items' => []])

<nav
    aria-label="Breadcrumb"
    class="mb-5 text-[12px] font-semibold tracking-[0.08em] text-[#2F795B] sm:text-[13px]"
>
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">

        {{-- Beranda --}}
        <li>
            <a
                href="{{ route('wisatawan.beranda') }}"
                class="transition hover:text-[#064631] hover:underline"
            >
                Beranda
            </a>
        </li>

        {{-- Halaman berikutnya --}}
        @foreach ($items as $item)

            <li aria-hidden="true">
                &rsaquo;
            </li>

            <li>
                @if (!empty($item['url']))
                    <a
                        href="{{ $item['url'] }}"
                        class="transition hover:text-[#064631] hover:underline"
                    >
                        {{ $item['label'] }}
                    </a>
                @else
                    <span
                        @if ($loop->last) aria-current="page" @endif
                    >
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>

        @endforeach

    </ol>
</nav>
