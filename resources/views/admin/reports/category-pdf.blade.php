<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>{{ $report['title'] }}</title>
<style>
@page { margin: 30px 30px 45px; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #243D32; }
h1 { font-size: 17px; color: #174B38; } h2 { font-size: 12px; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th, td { border: 1px solid #CBD9CF; padding: 5px; overflow-wrap: break-word; }
th { background: #174B38; color: white; text-align: left; }
thead { display: table-header-group; } tr { page-break-inside: avoid; }
tr:last-child { font-weight: bold; } p { margin: 4px 0; }
</style></head><body>
@php
    $format = static function ($value, $type) {
        if ($value === '') return '';
        if ($type === 'money') return 'Rp '.number_format((float) $value, 2, ',', '.');
        if ($type === 'integer' && is_numeric($value)) return number_format($value, 0, ',', '.');
        return (string) $value;
    };
@endphp
<strong>KEBUN RAYA MANGROVE SURABAYA | BRIDA Kota Surabaya</strong>
<h1>{{ $report['title'] }}</h1>
<p>{{ $report['period']['from'] }} s.d. {{ $report['period']['until'] }} | WIB</p>
<p>{{ $report['description'] }}</p>
<p>Dasar periode: {{ $report['basis'] }}</p>
<p>Data dihitung: {{ $report['generated_at'] }}</p>
<table class="w-full border-collapse text-left text-xs">
    <thead class="bg-[#E9F0EB] text-[#355D47]"><tr>
        @foreach ($report['columns'] as $column)
            <th scope="col" class="border border-[#D8E2DA] p-3">{{ $column }}</th>
        @endforeach
    </tr></thead>
    <tbody>
        @forelse ($report['rows'] as $row)
            <tr>
                @foreach ($row as $i => $value)
                    <td class="border border-[#D8E2DA] p-2 break-all">{{ $format($value, $report['types'][$i]) }}</td>
                @endforeach
            </tr>
        @empty
            <tr><td class="p-4 text-center" colspan="{{ count($report['columns']) }}">Tidak ada data pada periode ini.</td></tr>
        @endforelse
        <tr class="bg-[#E9F0EB] font-semibold">
            @foreach ($report['totals'] as $i => $value)
                <td class="border border-[#D8E2DA] p-2">{{ $format($value, $report['types'][$i]) }}</td>
            @endforeach
        </tr>
    </tbody>
</table>
<h2>Catatan Perhitungan</h2>
<ul>@foreach ($report['notes'] as $note)<li>{{ $note }}</li>@endforeach</ul>
</body></html>
