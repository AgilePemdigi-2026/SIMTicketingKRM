<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OperationalReportService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OperationalReportController extends Controller
{
    public function index(
        Request $request,
        OperationalReportService $service
    ): View|RedirectResponse {
        try {
            $filters = $this->resolvePeriod($request);
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }

            return redirect()
                ->route('admin.reports.index')
                ->withErrors($exception->errors());
        }

        $report = $service->generate(
            $filters['from'],
            $filters['until']
        );

        return view('admin.reports.index', [
            'report' => $report,
            'period' => $report['period'],
            'summary' => $report['summary'],
            'paymentMethods' => $report['payment_methods'],
            'salesChannels' => $report['sales_channels'],
            'daily' => $report['daily'],
            'generatedAt' => $report['generated_at'],
            'filters' => $filters,
        ]);
    }

    private function resolvePeriod(Request $request): array
    {
        $input = $request->query();

        $input['period'] = $input['period'] ?? 'monthly';

        $validated = Validator::make($input, [
            'period' => [
                'required',
                'string',
                Rule::in([
                    'daily',
                    'weekly',
                    'monthly',
                    'custom',
                ]),
            ],
        ], [
            'period.required' =>
                'Periode laporan wajib dipilih.',
            'period.string' =>
                'Periode laporan tidak valid.',
            'period.in' =>
                'Pilihan periode laporan tidak valid.',
        ])->validate();

        $selected = $validated['period'];

        $today = CarbonImmutable::now(
            'Asia/Jakarta'
        )->startOfDay();

        if ($selected === 'custom') {
            $dates = Validator::make($input, [
                'from' => [
                    'bail',
                    'required',
                    'string',
                    'date_format:Y-m-d',
                    'after_or_equal:0001-01-01',
                ],
                'until' => [
                    'bail',
                    'required',
                    'string',
                    'date_format:Y-m-d',
                    'after_or_equal:from',
                ],
            ], [
                'from.required' =>
                    'Tanggal awal wajib diisi.',
                'from.date_format' =>
                    'Format tanggal awal tidak valid.',
                'from.after_or_equal' =>
                    'Tanggal awal tidak valid.',
                'until.required' =>
                    'Tanggal akhir wajib diisi.',
                'until.date_format' =>
                    'Format tanggal akhir tidak valid.',
                'until.after_or_equal' =>
                    'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
            ])->validate();

            $from = CarbonImmutable::parse(
                $dates['from'],
                'Asia/Jakarta'
            )->startOfDay();

            $until = CarbonImmutable::parse(
                $dates['until'],
                'Asia/Jakarta'
            )->startOfDay();

            $days = (int) $from->diffInDays($until) + 1;

            if ($days > 366) {
                throw ValidationException::withMessages([
                    'until' =>
                        'Periode laporan maksimal 366 hari.',
                ]);
            }
        } else {
            $until = $today;

            $from = match ($selected) {
                'daily' => $today,
                'weekly' => $today->subDays(6),
                default => $today->startOfMonth(),
            };
        }

        return [
            'period' => $selected,
            'from' => $from->toDateString(),
            'until' => $until->toDateString(),
        ];
    }
}
