<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\TicketOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketValidationController extends Controller
{
    public function index(): View
    {
        return view('petugas.validasi-tiket.index', [
            'order' => null,
            'result' => null,
        ]);
    }

    public function verify(Request $request): View
    {
        $validated = $request->validate([
            'code' => ['required', 'uuid'],
        ]);

        $order = TicketOrder::query()
            ->with(['payment', 'items'])
            ->where('code', $validated['code'])
            ->first();

        if ($order === null) {
            $result = [
                'valid' => false,
                'message' => 'Kode tiket tidak ditemukan.',
            ];
        } elseif (
            $order->status !== 'paid' ||
            $order->payment?->status !== 'paid'
        ) {
            $result = [
                'valid' => false,
                'message' => 'Tiket belum lunas.',
            ];
        } elseif (
            $order->visit_date->toDateString() !==
            now('Asia/Jakarta')->toDateString()
        ) {
            $result = [
                'valid' => false,
                'message' => 'Tanggal kunjungan tiket tidak sesuai dengan hari ini.',
            ];
        } else {
            $result = [
                'valid' => true,
                'message' => 'Tiket ditemukan, pembayaran lunas, dan tanggal kunjungan sesuai. Status penggunaan tiket belum dapat diperiksa.',
            ];
        }

        return view('petugas.validasi-tiket.index', [
            'order' => $order,
            'result' => $result,
        ]);
    }
}
