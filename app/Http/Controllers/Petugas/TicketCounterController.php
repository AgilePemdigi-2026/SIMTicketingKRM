<?php

namespace App\Http\Controllers\Petugas;

use App\Contracts\TicketCatalogProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Petugas\PreviewCounterTicketRequest;
use App\Services\TicketPurchaseService;
use Illuminate\View\View;

class TicketCounterController extends Controller
{
    public function index(TicketCatalogProvider $catalog): View
    {
        return view('petugas.loket.index', [
            'tickets' => $catalog->activeTickets(),
            'today' => now('Asia/Jakarta')->toDateString(),
            'quote' => null,
        ]);
    }

    public function preview(
        PreviewCounterTicketRequest $request,
        TicketPurchaseService $service,
        TicketCatalogProvider $catalog
    ): View {
        $quote = $service->calculate($request->validated());

        return view('petugas.loket.index', [
            'tickets' => $catalog->activeTickets(),
            'today' => now('Asia/Jakarta')->toDateString(),
            'quote' => $quote,
        ]);
    }
}
