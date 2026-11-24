<?php

namespace App\Services;

use App\Models\TicketPayment;
use App\Models\TicketOrderVisitor;
use App\Support\TicketPurchaseDatabase;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class TicketRevenueMonitoringService
{
    private const TIMEZONE = 'Asia/Jakarta';

    private const MAX_PERIOD_DAYS = 366;

    private const STATUSES = [
        'paid',
        'pending',
        'cancelled',
        'expired',
    ];

    private const METHODS = ['cash', 'qris'];

    public function generate(array $filters): array
    {
        $filters = $this->validateFilters($filters);

        $from = CarbonImmutable::parse(
            $filters['from'],
            self::TIMEZONE
        )->startOfDay();

        $until = CarbonImmutable::parse(
            $filters['until'],
            self::TIMEZONE
        )->startOfDay();

        $days = (int) $from->diffInDays($until) + 1;

        if ($days > self::MAX_PERIOD_DAYS) {
            throw new InvalidArgumentException(
                'Periode maksimal 366 hari.'
            );
        }

        $connection = app(TicketPurchaseDatabase::class)->connection();
        $connectionName = $connection->getName();

        $paymentQuery = TicketPayment::on($connectionName)
            ->with([
                'order.items',
                'gatewayAttempts',
            ])
            ->whereHas('order');

        $this->applyFilters($paymentQuery, $filters);

        $payments = $paymentQuery
            ->orderByDesc('created_at')
            ->get();

        // Klasifikasi status menggunakan status pembayaran
        // dan attempt gateway terakhir.
        if (! empty($filters['statuses'])) {
            $payments = $payments->filter(
                fn ($payment) => in_array(
                    $this->classifyStatus($payment),
                    $filters['statuses'],
                    true
                )
            );
        }

        $paidPayments = $payments->filter(
            fn ($payment) => $this->classifyStatus($payment) === 'paid'
        );

        // Semua perhitungan uang menggunakan integer sen.
        $totalRevenueCents = $paidPayments->sum(
            fn ($payment) => $this->toCents($payment->amount)
        );

        $totalTickets = $payments->sum(
            fn ($payment) => $payment->order->items->sum(
                fn ($item) =>
                    $item->adult_quantity + $item->child_quantity
            )
        );

        $cancelled = $payments->filter(
    fn ($payment) =>
        $this->classifyStatus($payment) === 'cancelled'
);

$expired = $payments->filter(
    fn ($payment) =>
        $this->classifyStatus($payment) === 'expired'
);

$unpaid = $payments->filter(
    fn ($payment) =>
        $this->classifyStatus($payment) === 'pending'
);

        $summary = [
    'total_transaksi' => $payments->count(),
    'total_tiket' => $totalTickets,
    'total_pendapatan' => $this->money($totalRevenueCents),
    'transaksi_batal' => $cancelled->count(),
    'transaksi_kedaluwarsa' => $expired->count(),
    'belum_dibayar' => $unpaid->count(),
];

        $paymentMethods = [];

        foreach (self::METHODS as $method) {
            $methodPayments = $paidPayments->where(
                'method',
                $method
            );

            $paymentMethods[$method] = [
                'transaksi' => $methodPayments->count(),
                'pendapatan' => $this->money(
                    $methodPayments->sum(
                        fn ($payment) => $this->toCents(
                            $payment->amount
                        )
                    )
                ),
            ];
        }

        $dailyRevenue = $this->dailyRevenue(
            $paidPayments,
            $from,
            $until
        );

        // Statistik pengunjung hanya menampilkan data ketika
        // filter status mengikutsertakan transaksi lunas.
        $showVisitors = empty($filters['statuses'])
            || in_array('paid', $filters['statuses'], true);

        $dailyVisitors = $showVisitors
            ? $this->dailyVisitors(
                $connectionName,
                $filters,
                $from,
                $until
            )
            : $this->emptyDailyVisitors($from, $until);

        return [
            'period' => [
                'from' => $from->toDateString(),
                'until' => $until->toDateString(),
                'days' => $days,
                'timezone' => self::TIMEZONE,
            ],
            'filters' => $filters,
            'summary' => $summary,
            'payment_methods' => $paymentMethods,
            'daily_revenue' => $dailyRevenue,
            'daily_visitors' => $dailyVisitors,
            'generated_at' => CarbonImmutable::now(
                self::TIMEZONE
            )->toIso8601String(),
        ];
    }

    /**
     * Menentukan status transaksi berdasarkan status pembayaran
     * dan attempt gateway terakhir.
     */
    private function classifyStatus($payment): string
{
    // Pembayaran lunas selalu menjadi prioritas tertinggi.
    if ($payment->status === 'paid') {
        return 'paid';
    }

    // Status yang belum menghasilkan pembayaran.
    if (in_array(
        $payment->status,
        ['pending', 'under_review', 'rejected'],
        true
    )) {
        return 'pending';
    }

    // Status pembatalan dan kegagalan digabung.
    if (in_array(
        $payment->status,
        ['cancelled', 'failed'],
        true
    )) {
        return 'cancelled';
    }

    if ($payment->status === 'expired') {
        return 'expired';
    }

    // Gunakan attempt gateway terakhir.
    $latestAttempt = $payment->gatewayAttempts
        ->sortByDesc('id')
        ->first();

    if ($latestAttempt) {
        if (in_array(
            $latestAttempt->status,
            ['cancelled', 'failed'],
            true
        )) {
            return 'cancelled';
        }

        if ($latestAttempt->status === 'expired') {
            return 'expired';
        }
    }

    // Status non-terminal yang belum dibayar.
    return 'pending';
}

    private function validateFilters(array $filters): array
    {
        $validator = Validator::make($filters, [
            'from' => ['required', 'date_format:Y-m-d'],
            'until' => ['required', 'date_format:Y-m-d'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => [
                'string',
                'in:' . implode(',', self::STATUSES),
            ],
            'methods' => ['nullable', 'array'],
            'methods.*' => [
                'string',
                'in:' . implode(',', self::METHODS),
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        if ($validated['until'] < $validated['from']) {
            throw new InvalidArgumentException(
                'Tanggal akhir tidak boleh lebih awal dari tanggal awal.'
            );
        }

        $validated['statuses'] = array_values(array_unique(
            $validated['statuses'] ?? []
        ));

        $validated['methods'] = array_values(array_unique(
            $validated['methods'] ?? []
        ));

        return $validated;
    }

    private function applyFilters(
        Builder $query,
        array $filters
    ): void {
        $fromUtc = CarbonImmutable::parse(
            $filters['from'],
            self::TIMEZONE
        )->utc();

        $untilUtc = CarbonImmutable::parse(
            $filters['until'],
            self::TIMEZONE
        )->addDay()->startOfDay()->utc();

        // Periode transaksi berdasarkan tanggal pembuatan pesanan.
        $query->whereHas('order', function (Builder $order) use (
            $fromUtc,
            $untilUtc
        ) {
            $order->where('created_at', '>=', $fromUtc)
                ->where('created_at', '<', $untilUtc);
        });

        if (! empty($filters['methods'])) {
            $query->whereIn('method', $filters['methods']);
        }
    }

    private function dailyRevenue(
        $payments,
        CarbonImmutable $from,
        CarbonImmutable $until
    ): array {
        $result = [];

        for (
            $day = $from;
            $day->lte($until);
            $day = $day->addDay()
        ) {
            $date = $day->toDateString();

            $amountCents = $payments->filter(
                fn ($payment) =>
                    $payment->paid_at?->setTimezone(self::TIMEZONE)
                        ->toDateString() === $date
            )->sum(
                fn ($payment) => $this->toCents($payment->amount)
            );

            $result[] = [
                'date' => $date,
                'revenue' => $this->money($amountCents),
            ];
        }

        return $result;
    }

    /**
     * Menghitung orang berdasarkan data pengunjung aktual,
     * bukan jumlah tiket yang diterbitkan.
     */
    private function dailyVisitors(
        string $connection,
        array $filters,
        CarbonImmutable $from,
        CarbonImmutable $until
    ): array {
        $query = TicketOrderVisitor::on($connection)
            ->join(
                'ticket_order_items',
                'ticket_order_visitors.ticket_order_item_id',
                '=',
                'ticket_order_items.id'
            )
            ->join(
                'ticket_orders',
                'ticket_order_items.ticket_order_id',
                '=',
                'ticket_orders.id'
            )
            ->where('ticket_orders.status', 'paid')
            ->whereBetween(
                'ticket_orders.visit_date',
                [
                    $from->toDateString(),
                    $until->toDateString(),
                ]
            );

        if (! empty($filters['methods'])) {
            $query->whereExists(function ($subquery) use ($filters) {
                $subquery->selectRaw('1')
                    ->from('ticket_payments')
                    ->whereColumn(
                        'ticket_payments.ticket_order_id',
                        'ticket_orders.id'
                    )
                    ->whereIn(
                        'ticket_payments.method',
                        $filters['methods']
                    )
                    ->where('ticket_payments.status', 'paid');
            });
        }

        $counts = $query
            ->selectRaw(
                'ticket_orders.visit_date AS visit_day, COUNT(ticket_order_visitors.id) AS total'
            )
            ->groupBy('ticket_orders.visit_date')
            ->get()
            ->keyBy('visit_day');

        $result = [];

        for (
            $day = $from;
            $day->lte($until);
            $day = $day->addDay()
        ) {
            $date = $day->toDateString();

            $result[] = [
                'date' => $date,
                'visitors' => (int) ($counts[$date]->total ?? 0),
            ];
        }

        return $result;
    }

    private function emptyDailyVisitors(
        CarbonImmutable $from,
        CarbonImmutable $until
    ): array {
        $result = [];

        for (
            $day = $from;
            $day->lte($until);
            $day = $day->addDay()
        ) {
            $result[] = [
                'date' => $day->toDateString(),
                'visitors' => 0,
            ];
        }

        return $result;
    }

    /**
     * Mengubah nominal desimal menjadi integer sen.
     */
    private function toCents(mixed $amount): int
    {
        $value = (string) $amount;
        $negative = str_starts_with($value, '-');

        $value = ltrim($value, '-');
        [$whole, $fraction] = array_pad(
            explode('.', $value, 2),
            2,
            '0'
        );

        $cents = ((int) $whole * 100)
            + (int) str_pad(substr($fraction, 0, 2), 2, '0');

        return $negative ? -$cents : $cents;
    }

    private function money(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $cents = abs($cents);

        return $sign
            . intdiv($cents, 100)
            . '.'
            . str_pad((string) ($cents % 100), 2, '0');
    }
}
