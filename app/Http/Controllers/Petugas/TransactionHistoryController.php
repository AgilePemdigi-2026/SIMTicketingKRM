<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\TicketPayment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->string('tab')->lower()->value() ?: 'semua';
        $period = $request->string('period')->lower()->value() ?: 'hari-ini';

        $query = TicketPayment::query()
            ->with(['order.items'])
            ->whereHas('order');

        // 'online' adalah kanal pembelian Wisatawan.
        // NULL diperlakukan sebagai data lama hanya jika kolom masih nullable.
        if ($tab === 'loket') {
            $query->whereHas('order', fn (Builder $q) => $q->where('sale_channel', 'counter'));
        } elseif ($tab === 'online') {
            $query->whereHas('order', fn (Builder $q) => $q->where(function (Builder $channel) {
                $channel->where('sale_channel', 'online')->orWhereNull('sale_channel');
            }));
        }

        // Timestamp disimpan dengan acuan UTC. Satu hari operasional
        // harus dipotong menurut tanggal WIB (UTC+7), bukan tanggal UTC.
        $startTodayWib = CarbonImmutable::now('Asia/Jakarta')->startOfDay();

        if ($period === 'hari-ini' || $period === 'kemarin') {
            $startWib = $period === 'kemarin' ? $startTodayWib->subDay() : $startTodayWib;
            $query->where('created_at', '>=', $startWib->setTimezone('UTC'))
                ->where('created_at', '<', $startWib->addDay()->setTimezone('UTC'));
        } elseif ($period === 'custom') {
            $dates = $request->validate([
                'from' => ['required', 'date_format:Y-m-d'],
                'until' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            ], [
                'from.required' => 'Tanggal awal wajib diisi.',
                'until.required' => 'Tanggal akhir wajib diisi.',
                'until.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
            ]);

            if (! empty($dates['from'])) {
                $startWib = CarbonImmutable::parse($dates['from'], 'Asia/Jakarta')->startOfDay();
                $query->where('created_at', '>=', $startWib->setTimezone('UTC'));
            }

            if (! empty($dates['until'])) {
                $nextDayWib = CarbonImmutable::parse($dates['until'], 'Asia/Jakarta')->addDay()->startOfDay();
                $query->where('created_at', '<', $nextDayWib->setTimezone('UTC'));
            }
        }

        if ($request->filled('method') && $request->input('method') !== 'semua') {
            $query->where('method', $request->input('method'));
        }

        if ($request->filled('status') && $request->input('status') !== 'semua') {
            $query->where('status', $request->input('status'));
        }

        $payments = $query->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(10)->withQueryString();

        $todayCount = TicketPayment::query()
            ->whereHas('order')
            ->where('created_at', '>=', $startTodayWib->setTimezone('UTC'))
            ->where('created_at', '<', $startTodayWib->addDay()->setTimezone('UTC'))
            ->count();

        return view('petugas.riwayat-transaksi.index', [
            'payments' => $payments,
            'todayCount' => $todayCount,
            'tab' => $tab,
            'period' => $period,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $tab = $request->string('tab')->lower()->value() ?: 'semua';
        $period = $request->string('period')->lower()->value() ?: 'hari-ini';

        $query = TicketPayment::query()
            ->with(['order.items'])
            ->whereHas('order');

        // Filter kanal transaksi.
        if ($tab === 'loket') {
            $query->whereHas('order', fn (Builder $q) =>
                $q->where('sale_channel', 'counter')
            );
        } elseif ($tab === 'online') {
            $query->whereHas('order', fn (Builder $q) =>
                $q->where(function (Builder $channel) {
                    $channel->where('sale_channel', 'online')
                        ->orWhereNull('sale_channel');
                })
            );
        }

        // Filter periode berdasarkan WIB.
        $startTodayWib = CarbonImmutable::now('Asia/Jakarta')->startOfDay();

        if ($period === 'hari-ini' || $period === 'kemarin') {
            $startWib = $period === 'kemarin'
                ? $startTodayWib->subDay()
                : $startTodayWib;

            $query->where('created_at', '>=', $startWib->setTimezone('UTC'))
                ->where('created_at', '<', $startWib->addDay()->setTimezone('UTC'));
        } elseif ($period === 'custom') {
            $dates = $request->validate([
                'from' => ['required', 'date_format:Y-m-d'],
                'until' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            ], [
                'from.required' => 'Tanggal awal wajib diisi.',
                'until.required' => 'Tanggal akhir wajib diisi.',
                'until.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
            ]);

            if (! empty($dates['from'])) {
                $startWib = CarbonImmutable::parse(
                    $dates['from'], 'Asia/Jakarta'
                )->startOfDay();

                $query->where('created_at', '>=', $startWib->setTimezone('UTC'));
            }

            if (! empty($dates['until'])) {
                $nextDayWib = CarbonImmutable::parse(
                    $dates['until'], 'Asia/Jakarta'
                )->addDay()->startOfDay();

                $query->where('created_at', '<', $nextDayWib->setTimezone('UTC'));
            }
        }

        // Filter metode pembayaran.
        if ($request->filled('method') && $request->input('method') !== 'semua') {
            $query->where('method', $request->input('method'));
        }

        // Filter status pembayaran.
        if ($request->filled('status') && $request->input('status') !== 'semua') {
            $query->where('status', $request->input('status'));
        }

        $filename = 'riwayat-transaksi-' .
            CarbonImmutable::now('Asia/Jakarta')->format('Ymd-His') .
            '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 agar karakter dapat dibaca Excel.
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, "sep=;\r\n");

            fputcsv($handle, [
                'No. Transaksi',
                'Nama Pengunjung',
                'Jenis Tiket',
                'Kanal',
                'Metode Pembayaran',
                'Total (Rp)',
                'Status Pembayaran',
                'Waktu Transaksi (WIB)',
            ], ';', '"', '');

            // Mencegah teks dari pengguna dieksekusi sebagai formula Excel.
            $safeText = static function ($value): string {
                $text = (string) ($value ?? '');

                return preg_match('/^\s*[=+\-@]/u', $text)
                    ? "'" . $text
                    : $text;
            };

            // Mengambil seluruh data sesuai filter, bukan hanya satu halaman.
            foreach ($query->orderByDesc('created_at')
                ->orderByDesc('id')
                ->lazy(200) as $payment) {

                $order = $payment->order;

                $ticketNames = $order?->items
                    ?->pluck('ticket_name')
                    ->filter()
                    ->unique()
                    ->implode(', ') ?: '-';

                $channel = $order?->sale_channel === 'counter'
                    ? 'Loket'
                    : 'Online';

                $paymentStatus = match ($payment->status) {
                    'paid' => 'Lunas',
                    'pending' => 'Menunggu Pembayaran',
                    'expired' => 'Kedaluwarsa',
                    default => ucfirst((string) $payment->status),
                };

                fputcsv($handle, [
                    $safeText($order?->code),
                    $safeText($order?->booker_name),
                    $safeText($ticketNames),
                    $channel,
                    strtoupper((string) $payment->method),
                    $payment->amount,
                    $paymentStatus,
                    $payment->created_at
                        ?->timezone('Asia/Jakarta')
                        ->format('d/m/Y H:i:s'),
                ], ';', '"', '');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(TicketPayment $payment): View
    {
        $payment->load([
            'order.items',
            'order.issuedTickets.checkedInBy',
            'order.staff',
        ]);

        $order = $payment->order;
        $channelLabel = $order?->sale_channel === 'counter' ? 'Loket' : 'Online';

        // Loket: staf pembuat transaksi. Cash Booking: staf konfirmasi.
        // QRIS online diproses otomatis, tidak boleh dibuat-buat nama kasirnya.
        $cashierName = $order?->staff?->name;
        if (! $cashierName && $payment->confirmed_by) {
            $cashierName = User::query()->whereKey($payment->confirmed_by)->value('name');
        }

        $tickets = $order?->issuedTickets ?? collect();
        $totalTickets = $tickets->count();
        $usedTickets = $tickets->filter(fn ($ticket) =>
            $ticket->status === 'used' || $ticket->used_at !== null
        )->count();

        if ($totalTickets === 0) {
            $ticketStatus = 'E-Tiket Belum Terbit';
        } elseif ($usedTickets === 0) {
            $ticketStatus = 'Belum Check-In';
        } elseif ($usedTickets === $totalTickets) {
            $ticketStatus = 'Sudah Check-In';
        } else {
            $ticketStatus = 'Sebagian Check-In';
        }

        // Satu pesanan dapat punya beberapa E-Tiket dan waktu check-in.
        // Nilai ini adalah waktu check-in paling akhir yang tercatat.
        $lastCheckedInTicket = $tickets
            ->filter(fn ($ticket) => $ticket->used_at !== null)
            ->sortByDesc('used_at')
            ->first();

        $lastCheckInAt = $lastCheckedInTicket?->used_at;

        $validatorName = $lastCheckedInTicket?->checkedInBy?->name;

        return view('petugas.riwayat-transaksi.show', [
            'payment' => $payment,
            'order' => $order,
            'channelLabel' => $channelLabel,
            'ticketStatus' => $ticketStatus,
            'cashierName' => $cashierName,
            'validatorName' => $validatorName,
            'lastCheckInAt' => $lastCheckInAt,
        ]);
    }
}
