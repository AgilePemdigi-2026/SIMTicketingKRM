@props(['name'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }}
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="1.6"
     stroke-linecap="round"
     stroke-linejoin="round"
     aria-hidden="true">

    @switch($name)

        @case('leaf')
            <path d="M20 3C9 3 4 8 4 15a5 5 0 0 0 5 5C16 20 20 13 20 3Z"/>
            <path d="M3 21 16 8"/>
            @break

        @case('dashboard')
            <rect x="3" y="3" width="7" height="7"/>
            <rect x="14" y="3" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/>
            @break

        @case('transaction')
            <path d="M4 7h16m-4-4 4 4-4 4M20 17H4m4-4-4 4 4 4"/>
            @break

        @case('chart')
            <path d="M3 21h18M6 17V9m6 8V3m6 14V7"/>
            @break

        @case('ticket')
            <path d="M3 6h18v4a2 2 0 0 0 0 4v4H3v-4a2 2 0 0 0 0-4z"/>
            <path d="M15 6v3m0 3v2m0 3v1"/>
            @break

        @case('bike')
            <circle cx="5" cy="17" r="4"/>
            <circle cx="19" cy="17" r="4"/>
            <path d="m5 17 5-9 5 9H5m5-9h5l4 9M9 5h3m4 0h3"/>
            @break

        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="1"/>
            <path d="M7 3v4m10-4v4M3 10h18"/>
            @break

        @case('building')
            <path d="M4 21V5h11v16m0-12h5v12M2 21h20M8 8h3m-3 4h3m-3 4h3"/>
            @break

        @case('file')
            <path d="M6 3h8l5 5v13H6zM14 3v6h5M9 13h7m-7 4h7"/>
            @break

        @case('users')
            <circle cx="9" cy="8" r="3"/>
            <path d="M2 21v-3a7 7 0 0 1 14 0v3M17 5a3 3 0 0 1 0 6m1 3a5 5 0 0 1 4 5v2"/>
            @break

        @case('report')
            <rect x="5" y="3" width="14" height="18" rx="1"/>
            <path d="M8 7h8m-8 4h8m-8 4h5"/>
            @break

        @case('history')
            <path d="M3 11a9 9 0 1 1 2 7M3 4v7h7M12 7v5l4 2"/>
            @break

        @case('logout')
            <path d="M10 3h10v18H10M3 12h12m-4-4 4 4-4 4"/>
            @break

        @case('search')
            <circle cx="10" cy="10" r="6"/>
            <path d="m15 15 6 6"/>
            @break

        @case('bell')
            <path d="M5 17h14l-2-4V8A5 5 0 0 0 7 8v5zM10 21h4"/>
            @break

        @case('chevron')
            <path d="m7 9 5 5 5-5"/>
            @break

        @case('arrow')
            <path d="M4 12h16m-6-6 6 6-6 6"/>
            @break

        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16"/>
            @break

    @endswitch

</svg>
