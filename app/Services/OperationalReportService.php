<?php

namespace App\Services;

use App\Models\FacilityReservation;
use App\Models\FacilityReservationPayment;
use App\Models\IssuedTicket;
use App\Models\TicketOrder;
use App\Models\TicketPayment;
use App\Support\TicketPurchaseDatabase;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class OperationalReportService
{
    private const TIMEZONE = 'Asia/Jakarta';

    private const MAX_PERIOD_DAYS = 366;

    public function generate(string $from, string $until): array
    {
        $startWib = $this->parseDate($from);
        $lastDayWib = $this->parseDate($until);

        if ($lastDayWib->lt($startWib)) {
            throw new InvalidArgumentException(
                'Tanggal akhir tidak boleh lebih awal dari tanggal awal.'
            );
        }

        $days = (int) $startWib->diffInDays($lastDayWib) + 1;

        if ($days > self::MAX_PERIOD_DAYS) {
            throw new InvalidArgumentException(
                'Periode laporan maksimal 366 hari.'
            );
        }

        // Verify the same PostgreSQL database used by ticket purchases.
        app(TicketPurchaseDatabase::class)->connection();

        $startUtc = $startWib->utc();
        $endUtc = $lastDayWib->addDay()->startOfDay()->utc();

        $orders = $this->inPeriod(
            TicketOrder::query(),
            'created_at',
            $startUtc,
            $endUtc
        );

        $ticketPayments = $this->inPeriod(
            TicketPayment::query()->where('status', 'paid'),
            'paid_at',
            $startUtc,
            $endUtc
        );

        $issuedTickets = $this->inPeriod(
            IssuedTicket::query(),
            'created_at',
            $startUtc,
            $endUtc
        );

        $checkins = $this->inPeriod(
            IssuedTicket::query()->where('status', 'used'),
            'used_at',
            $startUtc,
            $endUtc
        );

        $reservations = FacilityReservation::query()
            ->whereBetween('visit_date', [$from, $until]);

        $reservationPayments = $this->inPeriod(
            FacilityReservationPayment::query()
                ->where('status', 'paid'),
            'paid_at',
            $startUtc,
            $endUtc
        );

        $ticketRevenue = $this->money(
            (clone $ticketPayments)->sum('amount')
        );

        $reservationRevenue = $this->money(
            (clone $reservationPayments)->sum('amount')
        );

        $summary = [
            'transaksi_tiket' => (clone $orders)->count(),

            'pembayaran_lunas' => (clone $ticketPayments)->count(),

            'pendapatan_tiket' => $ticketRevenue,

            'tiket_terbit' => (clone $issuedTickets)->count(),

            'pengunjung_checkin' => (clone $checkins)->count(),

            'reservasi_terjadwal' => (clone $reservations)->count(),

            'reservasi_lunas' =>
                (clone $reservationPayments)->count(),

            'pendapatan_reservasi' => $reservationRevenue,

            'total_pendapatan' => $this->addMoney(
                $ticketRevenue,
                $reservationRevenue
            ),
        ];

        $paymentMethods = $this->paymentMethods(
            $ticketPayments,
            $reservationPayments
        );

        $salesChannels = $this->salesChannels(
            $ticketPayments
        );

        $daily = $this->dailyReport(
            $startWib,
            $lastDayWib,
            $orders,
            $ticketPayments,
            $issuedTickets,
            $checkins,
            $reservations,
            $reservationPayments
        );

        return [
            'period' => [
                'from' => $from,
                'until' => $until,
                'days' => $days,
                'timezone' => self::TIMEZONE,
                'start_utc' => $startUtc->toIso8601String(),
                'end_utc_exclusive' => $endUtc->toIso8601String(),
            ],

            'summary' => $summary,

            'payment_methods' => $paymentMethods,

            'sales_channels' => $salesChannels,

            'daily' => $daily,

            'generated_at' => CarbonImmutable::now(
                self::TIMEZONE
            )->toIso8601String(),
        ];
    }

    /**
     * Validate an exact YYYY-MM-DD date.
     */
    private function parseDate(string $value): CarbonImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $value
        );

        if (
            $parsed === false
            || $parsed->format('Y-m-d') !== $value
            || ! preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)
            || substr($value, 0, 4) === '0000'
        ) {
            throw new InvalidArgumentException(
                'Format tanggal harus YYYY-MM-DD dan valid.'
            );
        }

        return CarbonImmutable::parse(
            $value,
            self::TIMEZONE
        )->startOfDay();
    }

    /**
     * Inclusive beginning and exclusive next-day boundary.
     */
    private function inPeriod(
        Builder $query,
        string $column,
        CarbonImmutable $startUtc,
        CarbonImmutable $endUtc
    ): Builder {
        return $query
            ->where($column, '>=', $startUtc)
            ->where($column, '<', $endUtc);
    }

    /**
     * Normalize a database decimal amount to two decimal places.
     */
    private function money(mixed $amount): string
    {
        $amount = (string) ($amount ?? '0');

        if (! preg_match('/^\d+(?:\.\d{1,2})?$/D', $amount)) {
            throw new InvalidArgumentException(
                'Nominal laporan tidak valid.'
            );
        }

        [$whole, $fraction] = array_pad(
            explode('.', $amount, 2),
            2,
            '0'
        );

        return $whole.'.'.str_pad($fraction, 2, '0');
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = explode(
            '.',
            $this->money($amount),
            2
        );

        return ((int) $whole * 100) + (int) $fraction;
    }

    private function addMoney(string ...$amounts): string
    {
        $total = 0;

        foreach ($amounts as $amount) {
            $total += $this->toCents($amount);
        }

        return intdiv($total, 100)
            .'.'
            .str_pad(
                (string) ($total % 100),
                2,
                '0',
                STR_PAD_LEFT
            );
    }

    /**
     * Payment method breakdown, including unknown legacy methods.
     */
    private function paymentMethods(
        Builder $ticketPayments,
        Builder $reservationPayments
    ): array {
        $result = [];

        foreach (['cash', 'qris', 'other'] as $method) {
            $tickets = clone $ticketPayments;
            $reservations = clone $reservationPayments;

            foreach ([$tickets, $reservations] as $query) {
                if ($method === 'other') {
                    $query->where(function (Builder $q) {
                        $q->whereNotIn('method', ['cash', 'qris'])
                            ->orWhereNull('method');
                    });
                } else {
                    $query->where('method', $method);
                }
            }

            $ticketRevenue = $this->money(
                (clone $tickets)->sum('amount')
            );

            $reservationRevenue = $this->money(
                (clone $reservations)->sum('amount')
            );

            $ticketCount = (clone $tickets)->count();

            $reservationCount = (clone $reservations)->count();

            $result[$method] = [
                'transaksi_tiket' => $ticketCount,

                'transaksi_reservasi' => $reservationCount,

                'total_transaksi' =>
                    $ticketCount + $reservationCount,

                'pendapatan_tiket' => $ticketRevenue,

                'pendapatan_reservasi' => $reservationRevenue,

                'total_pendapatan' => $this->addMoney(
                    $ticketRevenue,
                    $reservationRevenue
                ),
            ];
        }

        return $result;
    }

    /**
     * Sales channels apply only to ticket transactions.
     */
    private function salesChannels(
        Builder $ticketPayments
    ): array {
        $result = [];

        foreach (['online', 'counter', 'other'] as $channel) {
            $query = clone $ticketPayments;

            if ($channel === 'online') {
                $query->whereHas(
                    'order',
                    function (Builder $order) {
                        $order->where(function (Builder $q) {
                            $q->where('sale_channel', 'online')
                                ->orWhereNull('sale_channel');
                        });
                    }
                );
            } elseif ($channel === 'counter') {
                $query->whereHas(
                    'order',
                    fn (Builder $order) =>
                        $order->where('sale_channel', 'counter')
                );
            } else {
                $query->where(function (Builder $q) {
                    $q->whereDoesntHave('order')
                        ->orWhereHas(
                            'order',
                            fn (Builder $order) =>
                                $order->whereNotIn(
                                    'sale_channel',
                                    ['online', 'counter']
                                )
                        );
                });
            }

            $result[$channel] = [
                'transaksi_lunas' => (clone $query)->count(),

                'pendapatan_tiket' => $this->money(
                    (clone $query)->sum('amount')
                ),
            ];
        }

        return $result;
    }

    /**
     * Group timestamps by WIB calendar date.
     *
     * PostgreSQL timestamp types are handled separately.
     */
    private function aggregateByDay(
        Builder $query,
        string $column,
        bool $withTimezone,
        bool $includeAmount = false
    ): array {
        $dateExpression = $withTimezone
            ? "({$column} AT TIME ZONE 'Asia/Jakarta')::date"
            : "(({$column} AT TIME ZONE 'UTC') AT TIME ZONE 'Asia/Jakarta')::date";

        $select = $dateExpression
            .' AS report_date, COUNT(*) AS report_count';

        if ($includeAmount) {
            $select .= ', COALESCE(SUM(amount), 0) AS report_amount';
        }

        $rows = (clone $query)
            ->selectRaw($select)
            ->groupByRaw($dateExpression)
            ->toBase()
            ->get();

        return $rows->keyBy('report_date')->all();
    }

    /**
     * Daily operational report with zero-filled missing dates.
     */
    private function dailyReport(
        CarbonImmutable $startWib,
        CarbonImmutable $lastDayWib,
        Builder $orders,
        Builder $ticketPayments,
        Builder $issuedTickets,
        Builder $checkins,
        Builder $reservations,
        Builder $reservationPayments
    ): array {
        $orderDays = $this->aggregateByDay(
            $orders,
            'created_at',
            false
        );

        $ticketPaymentDays = $this->aggregateByDay(
            $ticketPayments,
            'paid_at',
            true,
            true
        );

        $issuedDays = $this->aggregateByDay(
            $issuedTickets,
            'created_at',
            true
        );

        $checkinDays = $this->aggregateByDay(
            $checkins,
            'used_at',
            true
        );

        // visit_date is a calendar date, not a UTC timestamp.
        $reservationDays = (clone $reservations)
            ->selectRaw(
                'visit_date AS report_date, COUNT(*) AS report_count'
            )
            ->groupBy('visit_date')
            ->toBase()
            ->get()
            ->keyBy('report_date')
            ->all();

        $reservationPaymentDays = $this->aggregateByDay(
            $reservationPayments,
            'paid_at',
            true,
            true
        );

        $result = [];

        for (
            $day = $startWib;
            $day->lte($lastDayWib);
            $day = $day->addDay()
        ) {
            $date = $day->toDateString();

            $ticketRevenue = $this->money(
                $ticketPaymentDays[$date]->report_amount ?? 0
            );

            $reservationRevenue = $this->money(
                $reservationPaymentDays[$date]->report_amount ?? 0
            );

            $result[] = [
                'date' => $date,

                'transaksi_tiket' =>
                    (int) ($orderDays[$date]->report_count ?? 0),

                'pembayaran_lunas' =>
                    (int) ($ticketPaymentDays[$date]->report_count ?? 0),

                'pendapatan_tiket' => $ticketRevenue,

                'tiket_terbit' =>
                    (int) ($issuedDays[$date]->report_count ?? 0),

                'pengunjung_checkin' =>
                    (int) ($checkinDays[$date]->report_count ?? 0),

                'reservasi_terjadwal' =>
                    (int) ($reservationDays[$date]->report_count ?? 0),

                'reservasi_lunas' =>
                    (int) ($reservationPaymentDays[$date]->report_count ?? 0),

                'pendapatan_reservasi' => $reservationRevenue,

                'total_pendapatan' => $this->addMoney(
                    $ticketRevenue,
                    $reservationRevenue
                ),
            ];
        }

        return $result;
    }
}
