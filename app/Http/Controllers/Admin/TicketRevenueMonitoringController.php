<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TicketRevenueMonitoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketRevenueMonitoringController extends Controller
{
    public function index(
        Request $request,
        TicketRevenueMonitoringService $service
    ) {
        $today = CarbonImmutable::now('Asia/Jakarta')->toDateString();

        $filters = [
            'from' => $request->input(
                'from',
                CarbonImmutable::now('Asia/Jakarta')
                    ->startOfMonth()->toDateString()
            ),
            'until' => $request->input('until', $today),
            'statuses' => $request->input('statuses', []),
            'methods' => $request->input('methods', []),
        ];

        $data = $service->generate($filters);

        return view('admin.revenue-monitoring.index', $data);
    }

    public function exportExcel(
        Request $request,
        TicketRevenueMonitoringService $service
    ): StreamedResponse {
        $data = $service->generate($this->filters($request));

        $filename = 'monitoring-pendapatan-'
            . $data['period']['from'] . '-'
            . $data['period']['until'] . '.csv';

        return response()->streamDownload(function () use ($data) {
            $output = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8 dengan benar.
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, ['MONITORING PENDAPATAN']);
            fputcsv($output, [
                'Periode',
                $data['period']['from'],
                $data['period']['until'],
            ]);
            fputcsv($output, []);
            fputcsv($output, ['Ringkasan', 'Jumlah']);

            foreach ($data['summary'] as $label => $value) {
                fputcsv($output, [$label, $value]);
            }

            fputcsv($output, []);
            fputcsv($output, [
                'Metode Pembayaran',
                'Transaksi',
                'Pendapatan',
            ]);

            foreach ($data['payment_methods'] as $method => $value) {
                fputcsv($output, [
                    strtoupper($method),
                    $value['transaksi'],
                    $value['pendapatan'],
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, ['Tanggal', 'Pendapatan', 'Pengunjung']);

            foreach ($data['daily_revenue'] as $index => $revenue) {
                fputcsv($output, [
                    $revenue['date'],
                    $revenue['revenue'],
                    $data['daily_visitors'][$index]['visitors'] ?? 0,
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(
        Request $request,
        TicketRevenueMonitoringService $service
    ) {
        $data = $service->generate($this->filters($request));

        return Pdf::loadView(
            'admin.revenue-monitoring.pdf',
            $data
        )->setPaper('a4', 'landscape')
            ->download('monitoring-pendapatan.pdf');
    }

    private function filters(Request $request): array
    {
        return [
            'from' => $request->input('from'),
            'until' => $request->input('until'),
            'statuses' => $request->input('statuses', []),
            'methods' => $request->input('methods', []),
        ];
    }
}
