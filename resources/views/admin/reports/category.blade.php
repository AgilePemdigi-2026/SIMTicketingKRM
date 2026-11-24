@extends('layouts.admin')
@section('title', $report['title'])
@section('content')
@php
    $format = static function ($value, $type) {
        if ($value === '') return '';
        if ($type === 'money') return 'Rp '.number_format((float) $value, 2, ',', '.');
        if ($type === 'integer' && is_numeric($value)) return number_format($value, 0, ',', '.');
        return (string) $value;
    };
@endphp
@php
    $period = ['period' => 'custom', 'from' => $report['period']['from'], 'until' => $report['period']['until']];
    $params = array_merge($period, ['category' => $report['category']]);
@endphp
<div class="mx-auto max-w-[1800px] space-y-4 text-[#405A4D]">
    <a class="text-xs underline" href="{{ route('admin.reports.index', $period) }}">&larr; Kembali ke Laporan</a>
    <header class="flex flex-wrap justify-between gap-3 border-b border-[#B8C9BD] pb-4">
        <div class="min-w-0 break-words space-y-1 text-xs">
            <h1 class="text-2xl font-medium text-[#0B372D]">{{ $report['title'] }}</h1>
<p>{{ $report['period']['from'] }} s.d. {{ $report['period']['until'] }} | WIB</p>
<p>{{ $report['description'] }}</p>
<p>Dasar periode: {{ $report['basis'] }}</p>
<p>Data dihitung: {{ $report['generated_at'] }}</p>
        </div>
        <div class="flex flex-wrap items-start gap-2">
            <a class="border border-[#174B38] bg-white p-3 text-xs" href="{{ route('admin.reports.export.excel', $params) }}">Export Excel</a>
            <a class="border border-[#174B38] bg-white p-3 text-xs" href="{{ route('admin.reports.export.pdf', $params) }}">Export PDF</a>
        </div>
    </header>
    <section tabindex="0" role="region" aria-label="Tabel laporan kategori" class="min-w-0 max-w-full overflow-x-auto bg-white border border-[#D8E2DA] focus-visible:outline-2 focus-visible:outline-[#527A65]">
<table class="w-full min-w-[640px] border-collapse text-left text-xs">
    <thead class="bg-[#E9F0EB] text-[#355D47]"><tr>
        @foreach ($report['columns'] as $column)
            <th scope="col" class="border border-[#D8E2DA] p-3">{{ $column }}</th>
        @endforeach
    </tr></thead>
    <tbody>
        @forelse ($report['rows'] as $row)
            <tr>
                @foreach ($row as $i => $value)
                    <td class="border border-[#D8E2DA] p-2 break-words">{{ $format($value, $report['types'][$i]) }}</td>
                @endforeach
            </tr>
        @empty
            <tr><td class="p-4 text-center" colspan="{{ count($report['columns']) }}">Tidak ada data pada periode ini.</td></tr>
        @endforelse
        <tr class="bg-[#E9F0EB] font-semibold">
            @foreach ($report['totals'] as $i => $value)
                <td class="whitespace-nowrap border border-[#D8E2DA] p-2">{{ $format($value, $report['types'][$i]) }}</td>
            @endforeach
        </tr>
    </tbody>
</table>
    </section>
    <section class="bg-[#E9F0EB] p-4 text-xs leading-6">
<h2>Catatan Perhitungan</h2>
<ul>@foreach ($report['notes'] as $note)<li>{{ $note }}</li>@endforeach</ul>
    </section>
</div>
@endsection
