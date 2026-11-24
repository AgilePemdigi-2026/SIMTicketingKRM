<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\IssuedTicket;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TicketValidationController extends Controller
{
    public function index(Request $request): View
    {
        // Halaman sukses hanya ditampilkan sesudah check-in berhasil.
        // Data tiket tetap dimuat ulang dari database, bukan dari input browser.
        $checkedTicketId = $request->session()->get('checked_in_ticket_id');
        if (is_numeric($checkedTicketId) && (int) $checkedTicketId > 0) {
            $checkedTicket = IssuedTicket::query()
                ->with(['order.payment', 'order.issuedTickets', 'item', 'visitor'])
                ->whereKey((int) $checkedTicketId)
                ->where('status', 'used')
                ->whereNotNull('used_at')
                ->first();

            if ($checkedTicket !== null) {
                return view('petugas.validasi-tiket.success', [
                    'ticket' => $checkedTicket,
                    'order' => $checkedTicket->order,
                    'staffName' => $request->session()->get('checkin_staff_name', $request->user()?->name ?? '-'),
                ]);
            }
        }

        // Pencarian manual adalah fitur pencarian tiket, BUKAN bypass validasi.
        // Check-in masih wajib menggunakan QR token individual yang sudah diterbitkan.
        $rawCode = $request->query('code', '');
        $code = is_string($rawCode) ? trim($rawCode) : '';
        $ticket = null;
        $order = null;
        $result = null;
        $matches = collect();

        if ($code !== '') {
            if (strlen($code) > 100) {
                $result = $this->invalidResult('Kode pencarian maksimal 100 karakter.');
            } else {
                $matches = $this->findByManualCode($code);

                if ($matches->count() === 1) {
                    $ticket = $matches->first();
                    $order = $ticket->order;
                    $result = $this->checkTicket($ticket);
                } elseif ($matches->isEmpty()) {
                    $result = $this->invalidResult('Tiket tidak ditemukan. Periksa kembali kode yang dimasukkan.');
                } elseif ($matches->count() > 20) {
                    $result = $this->invalidResult('Terlalu banyak tiket yang cocok. Gunakan kode E-Tiket individual.');
                    $matches = collect();
                }
                // Beberapa tiket dalam satu booking harus dipilih satu per satu.
            }
        }

        return view('petugas.validasi-tiket.index', compact(
            'ticket', 'order', 'result', 'matches', 'code'
        ));
    }

    public function verify(Request $request): View
    {
        // Kontrak token 64 alfanumerik dari scanner lama tetap dipertahankan.
        $validated = $request->validate([
            'qr_token' => ['required', 'string', 'size:64', 'alpha_num'],
        ]);

        $ticket = IssuedTicket::query()
            ->with(['order.payment', 'order.issuedTickets', 'item', 'visitor'])
            ->where('qr_token', $validated['qr_token'])
            ->first();

        $result = $this->checkTicket($ticket);
        $order = $ticket?->order;
        $matches = collect();
        $code = '';

        return view('petugas.validasi-tiket.index', compact(
            'ticket', 'order', 'result', 'matches', 'code'
        ));
    }

    public function checkIn(Request $request, TicketPurchaseDatabase $database): RedirectResponse
    {
        $validated = $request->validate([
            'qr_token' => ['required', 'string', 'size:64', 'alpha_num'],
        ]);

        // Ambil ID petugas yang sedang login.
        $staffId = $request->user()->getAuthIdentifier();

        $result = $database->connection()->transaction(function () use ($validated, $staffId) {
            // Lock tetap penting agar dua petugas tidak dapat
            // melakukan check-in pada tiket yang sama.
            $ticket = IssuedTicket::query()
                ->where('qr_token', $validated['qr_token'])
                ->lockForUpdate()
                ->first();

            if ($ticket !== null) {
                $ticket->load(['order.payment']);
            }

            $result = $this->checkTicket($ticket);

            if (! $result['valid']) {
                return $result;
            }

            // Simpan status, waktu, dan petugas validasi.
            $ticket->update([
                'status' => 'used',
                'used_at' => now(),
                'checked_in_by' => $staffId,
            ]);

            return [
                'valid' => true,
                'message' => 'Check-in berhasil. E-Tiket telah ditandai sebagai digunakan.',
                'ticket_id' => $ticket->getKey(),
            ];
        }, 3);

        if (! $result['valid']) {
            return redirect()
                ->route('petugas.validasi-tiket.index')
                ->withErrors(['qr_token' => $result['message']]);
        }

        // Tampilkan halaman sukses setelah transaksi berhasil.
        return redirect()->route('petugas.validasi-tiket.index')
            ->with('success', $result['message'])
            ->with('checked_in_ticket_id', $result['ticket_id'])
            ->with('checkin_staff_name', $request->user()?->name ?? '-');
    }


    /** @return Collection<int, IssuedTicket> */
    private function findByManualCode(string $code): Collection
    {
        $query = IssuedTicket::query()
            ->with([
                'order.payment',
                'order.issuedTickets',
                'item',
                'visitor',
            ]);

        // 1. Pencarian kode Cash Booking, misalnya CASH-0009.
        if (preg_match('/^CASH-([0-9]+)$/i', $code, $matches) === 1) {
            $number = ltrim($matches[1], '0');

            $orderId = filter_var(
                $number,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($orderId === false) {
                return collect();
            }

            return $query
                ->whereHas('order', function ($order) use ($orderId) {
                    $order->whereKey($orderId)
                        ->where('sale_channel', 'online');
                })
                ->limit(21)
                ->get();
        }

        // 2. Pencarian token QR E-Tiket asli (64 karakter).
        if (preg_match('/^[A-Za-z0-9]{64}$/', $code) === 1) {
            return $query
                ->where('qr_token', $code)
                ->limit(21)
                ->get();
        }

        // 3. Pencarian UUID tiket atau UUID booking.
        // Hanya jalankan query UUID jika format input benar.
        if (\Illuminate\Support\Str::isUuid($code)) {
            return $query
                ->where(function ($ticketQuery) use ($code) {
                    $ticketQuery
                        ->where('ticket_code', $code)
                        ->orWhereHas('order', function ($order) use ($code) {
                            $order->where('code', $code);
                        });
                })
                ->limit(21)
                ->get();
        }

        // Format kode tidak dikenali.
        // Kembalikan hasil kosong tanpa membuat error database.
        return collect();
    }

    private function invalidResult(string $message): array
    {
        return [
            'valid' => false,
            'message' => $message,
            'checks' => [
                'ticket' => false,
                'payment' => false,
                'date' => false,
                'unused' => false,
            ],
        ];
    }

    private function checkTicket(?IssuedTicket $ticket): array
    {
        if ($ticket === null) {
            return $this->invalidResult('E-Tiket tidak ditemukan.');
        }

        $order = $ticket->order;
        $isActive = $ticket->status === 'active';
        $isUnused = $ticket->used_at === null && $ticket->status !== 'used';
        $isPaid = $order !== null
            && $order->status === 'paid'
            && $order->payment?->status === 'paid';
        $isToday = $order?->visit_date?->toDateString() === now('Asia/Jakarta')->toDateString();

        $checks = [
            'ticket' => $isActive,
            'payment' => $isPaid,
            'date' => $isToday,
            'unused' => $isUnused,
        ];

        // Pertahankan prioritas dan teks penolakan dari controller lama.
        if (! $isActive || ! $isUnused) {
            $message = 'E-Tiket sudah digunakan atau tidak aktif.';
        } elseif (! $isPaid) {
            $message = 'Pembayaran tiket belum lunas.';
        } elseif (! $isToday) {
            $message = 'Tanggal kunjungan tidak sesuai dengan hari ini.';
        } else {
            $message = 'E-Tiket valid dan siap untuk check-in.';
        }

        return [
            'valid' => $isActive && $isUnused && $isPaid && $isToday,
            'message' => $message,
            'checks' => $checks,
        ];
    }
}
