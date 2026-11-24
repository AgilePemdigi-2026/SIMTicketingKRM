<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OperationalReportService;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class OperationalReportController extends Controller
{
    public function index(
        OperationalReportService $service
    ): View {
        $today = CarbonImmutable::now('Asia/Jakarta');

        $from = $today->startOfMonth()->toDateString();
        $until = $today->toDateString();

        $report = $service->generate($from, $until);

        return view('admin.reports.index', [
            'report' => $report,
            'period' => $report['period'],
            'summary' => $report['summary'],
            'paymentMethods' => $report['payment_methods'],
            'salesChannels' => $report['sales_channels'],
            'daily' => $report['daily'],
            'generatedAt' => $report['generated_at'],
        ]);
    }
}
