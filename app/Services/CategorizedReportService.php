<?php

namespace App\Services;

use App\Support\TicketPurchaseDatabase;
use Carbon\CarbonImmutable;
use Illuminate\Database\Connection;
use InvalidArgumentException;

class CategorizedReportService
{
    public const CATEGORIES = [
        'ticket-sales' => 'Laporan Penjualan Tiket',
        'visitors' => 'Laporan Pengunjung',
        'revenue' => 'Laporan Pendapatan',
        'cash' => 'Laporan Transaksi Cash',
        'qris' => 'Laporan Transaksi QRIS',
        'reservations' => 'Laporan Reservasi',
        'checkins' => 'Laporan Check-in',
    ];

    public function __construct(
        private OperationalReportService $reports,
        private TicketPurchaseDatabase $database
    ) {}

    public function generate(
        string $category,
        string $from,
        string $until
    ): array {
        if (! array_key_exists($category, self::CATEGORIES)) {
            throw new InvalidArgumentException(
                'Kategori laporan tidak tersedia.'
            );
        }

        // Reuse the existing period validation and aggregation.
        $report = $this->reports->generate($from, $until);

        $db = $this->database->connection();

        $start = CarbonImmutable::parse(
            $from,
            'Asia/Jakarta'
        )->startOfDay()->utc();

        $end = CarbonImmutable::parse(
            $until,
            'Asia/Jakarta'
        )->addDay()->startOfDay()->utc();

        $result = match ($category) {
            'ticket-sales' => $this->ticketSales(
                $db, $start, $end
            ),
            'visitors' => $this->visitors(
                $db, $report, $from, $until
            ),
            'revenue' => $this->revenue($report),
            'cash' => $this->payments(
                $db, 'cash', $start, $end
            ),
            'qris' => $this->payments(
                $db, 'qris', $start, $end
            ),
            'reservations' => $this->reservations(
                $db, $from, $until
            ),
            'checkins' => $this->checkins(
                $db, $start, $end
            ),
        };

        return array_merge([
            'category' => $category,
            'title' => self::CATEGORIES[$category],
            'period' => $report['period'],
            'generated_at' => $report['generated_at'],
        ], $result);
    }

    private function ticketSales(
        Connection $db,
        CarbonImmutable $start,
        CarbonImmutable $end
    ): array {
        $records = $db->table('ticket_order_items as i')
            ->join(
                'ticket_orders as o',
                'o.id',
                '=',
                'i.ticket_order_id'
            )
            ->join(
                'ticket_payments as p',
                'p.ticket_order_id',
                '=',
                'o.id'
            )
            ->where('o.status', 'paid')
            ->where('p.status', 'paid')
            ->where('p.paid_at', '>=', $start)
            ->where('p.paid_at', '<', $end)
            ->selectRaw(
                'i.ticket_name,
                SUM(i.adult_quantity) AS adults,
                SUM(i.child_quantity) AS children,
                SUM(i.subtotal) AS amount'
            )
            ->groupBy('i.ticket_name')
            ->orderBy('i.ticket_name')
            ->get();

        $rows = $records->map(fn ($record) => [
            $record->ticket_name,
            (int) $record->adults,
            (int) $record->children,
            (int) $record->adults + (int) $record->children,
            $this->money($record->amount),
        ])->all();

        $adults = array_sum(array_column($rows, 1));
        $children = array_sum(array_column($rows, 2));

        return [
            'description' =>
                'Penjualan tiket berdasarkan pembayaran lunas.',
            'basis' => 'Tanggal pembayaran lunas (WIB)',
            'columns' => [
                'Jenis Tiket',
                'Dewasa',
                'Anak',
                'Total Tiket',
                'Nominal Penjualan',
            ],
            'types' => [
                'text', 'integer', 'integer', 'integer', 'money',
            ],
            'rows' => $rows,
            'totals' => [
                'Total',
                $adults,
                $children,
                $adults + $children,
                $this->sumMoney($rows, 4),
            ],
            'notes' => [
                'Hanya pesanan dengan pembayaran lunas.',
                'Nominal menggunakan subtotal item tiket.',
            ],
        ];
    }

    private function visitors(
        Connection $db,
        array $report,
        string $from,
        string $until
    ): array {
        $scheduled = $db->table('issued_tickets as t')
            ->join(
                'ticket_orders as o',
                'o.id',
                '=',
                't.ticket_order_id'
            )
            ->where('o.status', 'paid')
            ->whereBetween('o.visit_date', [$from, $until])
            ->whereIn('t.status', ['active', 'used'])
            ->selectRaw(
                "o.visit_date,
                COUNT(*) AS scheduled,
                COUNT(*) FILTER (
                    WHERE t.status = 'active'
                ) AS unused,
                COUNT(*) FILTER (
                    WHERE t.status = 'used'
                ) AS used"
            )
            ->groupBy('o.visit_date')
            ->get()
            ->keyBy('visit_date');

        $rows = [];

        foreach ($report['daily'] as $day) {
            $record = $scheduled->get($day['date']);

            $rows[] = [
                $day['date'],
                (int) ($record->scheduled ?? 0),
                (int) ($record->unused ?? 0),
                (int) ($record->used ?? 0),
                (int) $day['pengunjung_checkin'],
            ];
        }

        return [
            'description' =>
                'Pengunjung terjadwal dan kunjungan aktual.',
            'basis' => 'Tanggal kunjungan dan waktu check-in (WIB)',
            'columns' => [
                'Tanggal',
                'Pengunjung Terjadwal',
                'Belum Digunakan',
                'Tiket Digunakan',
                'Check-in Aktual',
            ],
            'types' => [
                'date', 'integer', 'integer', 'integer', 'integer',
            ],
            'rows' => $rows,
            'totals' => [
                'Total',
                array_sum(array_column($rows, 1)),
                array_sum(array_column($rows, 2)),
                array_sum(array_column($rows, 3)),
                array_sum(array_column($rows, 4)),
            ],
            'notes' => [
                'Pengunjung terjadwal menggunakan tanggal kunjungan.',
                'Status tiket digunakan adalah status saat laporan dibuat.',
                'Check-in aktual menggunakan waktu check-in.',
                'Pengunjung dihitung per tiket, bukan individu unik.',
            ],
        ];
    }

    private function revenue(array $report): array
    {
        $rows = array_map(fn ($day) => [
            $day['date'],
            $day['pendapatan_tiket'],
            $day['pendapatan_reservasi'],
            $day['total_pendapatan'],
        ], $report['daily']);

        return [
            'description' =>
                'Rekap pendapatan tiket dan reservasi fasilitas.',
            'basis' => 'Tanggal pembayaran lunas (WIB)',
            'columns' => [
                'Tanggal',
                'Pendapatan Tiket',
                'Pendapatan Reservasi',
                'Total Pendapatan',
            ],
            'types' => [
                'date', 'money', 'money', 'money',
            ],
            'rows' => $rows,
            'totals' => [
                'Total',
                $report['summary']['pendapatan_tiket'],
                $report['summary']['pendapatan_reservasi'],
                $report['summary']['total_pendapatan'],
            ],
            'notes' => [
                'Hanya pembayaran berstatus lunas.',
                'Pendapatan bruto sebelum biaya gateway atau refund.',
            ],
        ];
    }

    private function payments(
        Connection $db,
        string $method,
        CarbonImmutable $start,
        CarbonImmutable $end
    ): array {
        $ticketPayments = $db->table('ticket_payments as p')
            ->join(
                'ticket_orders as o',
                'o.id',
                '=',
                'p.ticket_order_id'
            )
            ->where('p.method', $method)
            ->where('p.status', 'paid')
            ->where('p.paid_at', '>=', $start)
            ->where('p.paid_at', '<', $end)
            ->select([
                'p.code',
                'p.paid_at',
                'p.amount',
                'o.sale_channel',
            ])
            ->get();

        $rows = $ticketPayments->map(fn ($payment) => [
            (string) $payment->code,
            $this->wib($payment->paid_at),
            'Tiket',
            match ($payment->sale_channel) {
                'counter' => 'Loket',
                'online', null => 'Online',
                default => 'Lainnya',
            },
            $this->money($payment->amount),
        ])->all();

        $reservationPayments = $db
            ->table('facility_reservation_payments as p')
            ->join(
                'facility_reservations as r',
                'r.id',
                '=',
                'p.facility_reservation_id'
            )
            ->where('p.method', $method)
            ->where('p.status', 'paid')
            ->where('p.paid_at', '>=', $start)
            ->where('p.paid_at', '<', $end)
            ->select([
                'r.invoice_number',
                'p.paid_at',
                'p.amount',
            ])
            ->get();

        foreach ($reservationPayments as $payment) {
            $rows[] = [
                $payment->invoice_number,
                $this->wib($payment->paid_at),
                'Reservasi',
                'Reservasi Fasilitas',
                $this->money($payment->amount),
            ];
        }

        usort($rows, static fn ($a, $b) =>
            strcmp($b[1], $a[1])
            ?: strcmp($a[0], $b[0])
        );

        return [
            'description' =>
                'Riwayat pembayaran lunas melalui '
                .strtoupper($method).'.',
            'basis' => 'Tanggal pembayaran lunas (WIB)',
            'columns' => [
                'Kode Transaksi',
                'Waktu Pembayaran',
                'Jenis Transaksi',
                'Kanal Penjualan',
                'Nominal',
            ],
            'types' => [
                'text', 'datetime', 'text', 'text', 'money',
            ],
            'rows' => $rows,
            'totals' => [
                'Total',
                '',
                count($rows).' transaksi',
                '',
                $this->sumMoney($rows, 4),
            ],
            'notes' => [
                'Pembayaran pending dan gagal tidak dihitung.',
                'Nominal Cash bukan uang tunai sebelum kembalian.',
                'Kode reservasi menggunakan nomor invoice.',
            ],
        ];
    }

    private function reservations(
        Connection $db,
        string $from,
        string $until
    ): array {
        $records = $db->table('facility_reservations as r')
            ->leftJoin(
                'facility_reservation_payments as p',
                'p.facility_reservation_id',
                '=',
                'r.id'
            )
            ->whereBetween('r.visit_date', [$from, $until])
            ->select([
                'r.invoice_number',
                'r.facility_name',
                'r.visit_date',
                'r.participant_count',
                'r.administrative_status',
                'r.total_amount',
                'p.status as payment_status',
                'p.amount as payment_amount',
            ])
            ->orderBy('r.visit_date')
            ->orderBy('r.id')
            ->get();

        $rows = $records->map(fn ($record) => [
            $record->invoice_number,
            $record->facility_name,
            $record->visit_date,
            (int) $record->participant_count,
            $this->statusLabel($record->administrative_status),
            $this->statusLabel(
                $record->payment_status ?? 'unpaid'
            ),
            $this->money($record->total_amount),
            $record->payment_status === 'paid'
                ? $this->money($record->payment_amount)
                : '0.00',
        ])->all();

        return [
            'description' =>
                'Rekap reservasi fasilitas berdasarkan jadwal.',
            'basis' => 'Tanggal kunjungan reservasi',
            'columns' => [
                'Nomor Invoice',
                'Fasilitas',
                'Tanggal Kunjungan',
                'Peserta',
                'Status Reservasi',
                'Status Pembayaran',
                'Total Tagihan',
                'Nominal Lunas',
            ],
            'types' => [
                'text', 'text', 'date', 'integer',
                'text', 'text', 'money', 'money',
            ],
            'rows' => $rows,
            'totals' => [
                'Total',
                count($rows).' reservasi',
                '',
                array_sum(array_column($rows, 3)),
                '',
                '',
                $this->sumMoney($rows, 6),
                $this->sumMoney($rows, 7),
            ],
            'notes' => [
                'Mencakup seluruh status reservasi pada periode.',
                'Status pembayaran adalah status saat laporan dibuat.',
                'Nominal lunas tidak dibatasi tanggal pembayaran.',
                'Gunakan Laporan Pendapatan untuk periode pembayaran.',
            ],
        ];
    }

    private function checkins(
        Connection $db,
        CarbonImmutable $start,
        CarbonImmutable $end
    ): array {
        $records = $db->table('issued_tickets as t')
            ->join(
                'ticket_order_items as i',
                'i.id',
                '=',
                't.ticket_order_item_id'
            )
            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                't.checked_in_by'
            )
            ->where('t.status', 'used')
            ->where('t.used_at', '>=', $start)
            ->where('t.used_at', '<', $end)
            ->select([
                't.ticket_code',
                'i.ticket_name',
                't.used_at',
                'u.name as staff_name',
            ])
            ->orderByDesc('t.used_at')
            ->orderByDesc('t.id')
            ->get();

        $rows = $records->map(fn ($record) => [
            (string) $record->ticket_code,
            $record->ticket_name,
            $this->wib($record->used_at),
            $record->staff_name ?? 'Tidak tercatat',
            'Sudah Check-in',
        ])->all();

        return [
            'description' =>
                'Riwayat validasi kedatangan wisatawan.',
            'basis' => 'Waktu check-in aktual (WIB)',
            'columns' => [
                'Kode E-Tiket',
                'Jenis Tiket',
                'Waktu Check-in',
                'Petugas',
                'Status',
            ],
            'types' => [
                'text', 'text', 'datetime', 'text', 'text',
            ],
            'rows' => $rows,
            'totals' => [
                'Total',
                '',
                '',
                '',
                count($rows).' check-in',
            ],
            'notes' => [
                'Hanya tiket berstatus used dengan waktu check-in.',
                'Petugas yang tidak tercatat ditampilkan apa adanya.',
                'Token QR dan identitas wisatawan tidak diekspor.',
            ],
        ];
    }

    private function money(mixed $value): string
    {
        return bcadd((string) ($value ?? '0'), '0', 2);
    }

    private function sumMoney(array $rows, int $column): string
    {
        $total = '0.00';

        foreach ($rows as $row) {
            $total = bcadd(
                $total,
                (string) $row[$column],
                2
            );
        }

        return $total;
    }

    private function wib(mixed $value): string
    {
        return CarbonImmutable::parse($value, 'UTC')
            ->timezone('Asia/Jakarta')
            ->format('Y-m-d H:i:s');
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'confirmed' => 'Dikonfirmasi',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'completed' => 'Selesai',
            'paid' => 'Lunas',
            'unpaid' => 'Belum Bayar',
            'expired' => 'Kedaluwarsa',
            'failed' => 'Gagal',
            'under_review' => 'Menunggu Verifikasi',
            default => ucfirst(
                str_replace('_', ' ', $status)
            ),
        };
    }
}
