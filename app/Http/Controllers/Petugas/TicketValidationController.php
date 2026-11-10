<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\IssuedTicket;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketValidationController extends Controller
{
    public function index(): View
    {
        return view('petugas.validasi-tiket.index', [
            'order' => null,
            'ticket' => null,
            'result' => null,
        ]);
    }

    public function verify(Request $request): View
    {
        $validated = $request->validate([
            'qr_token' => ['required', 'string', 'size:64', 'alpha_num'],
        ]);

        $ticket = IssuedTicket::query()
            ->with(['order.payment', 'item', 'visitor'])
            ->where('qr_token', $validated['qr_token'])
            ->first();

        $result = $this->checkTicket($ticket);

        return view('petugas.validasi-tiket.index', [
            'order' => $ticket?->order,
            'ticket' => $ticket,
            'result' => $result,
        ]);
    }

    public function checkIn(
        Request $request,
        TicketPurchaseDatabase $database
    ): RedirectResponse {
        $validated = $request->validate([
            'qr_token' => ['required', 'string', 'size:64', 'alpha_num'],
        ]);

        $result = $database->connection()->transaction(
            function () use ($validated) {
                // Kunci tiket agar dua petugas tidak bisa
                // menggunakan tiket yang sama secara bersamaan.
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

                $ticket->update([
                    'status' => 'used',
                    'used_at' => now(),
                ]);

                return [
                    'valid' => true,
                    'message' => 'Check-in berhasil. E-Tiket telah ditandai sebagai digunakan.',
                ];
            },
            3
        );

        if (! $result['valid']) {
            return redirect()
                ->route('petugas.validasi-tiket.index')
                ->withErrors([
                    'qr_token' => $result['message'],
                ]);
        }

        return redirect()
            ->route('petugas.validasi-tiket.index')
            ->with('success', $result['message']);
    }

    private function checkTicket(?IssuedTicket $ticket): array
    {
        if ($ticket === null) {
            return [
                'valid' => false,
                'message' => 'E-Tiket tidak ditemukan.',
            ];
        }

        if ($ticket->status !== 'active' || $ticket->used_at !== null) {
            return [
                'valid' => false,
                'message' => 'E-Tiket sudah digunakan atau tidak aktif.',
            ];
        }

        $order = $ticket->order;

        if (
            $order === null ||
            $order->status !== 'paid' ||
            $order->payment?->status !== 'paid'
        ) {
            return [
                'valid' => false,
                'message' => 'Pembayaran tiket belum lunas.',
            ];
        }

        if (
            $order->visit_date->toDateString() !==
            now('Asia/Jakarta')->toDateString()
        ) {
            return [
                'valid' => false,
                'message' => 'Tanggal kunjungan tidak sesuai dengan hari ini.',
            ];
        }

        return [
            'valid' => true,
            'message' => 'E-Tiket valid dan siap untuk check-in.',
        ];
    }
}
