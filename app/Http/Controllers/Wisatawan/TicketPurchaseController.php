<?php

namespace App\Http\Controllers\Wisatawan;

use App\Contracts\TicketCatalogProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutTicketPurchaseRequest;
use App\Models\TicketOrder;
use App\Services\TicketPurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketPurchaseController extends Controller
{
    public function index(TicketCatalogProvider $catalog): JsonResponse
    {
        return response()->json(['is_dummy' => true, 'currency' => 'IDR', 'tickets' => $catalog->activeTickets()]);
    }

    public function store(CheckoutTicketPurchaseRequest $request, TicketPurchaseService $service): JsonResponse
    {
        $order = $service->create($request->user(), $request->validated());

        return response()->json(['is_dummy' => true, 'currency' => 'IDR', 'order' => $order], 201);
    }

    public function show(Request $request, string $code): JsonResponse
    {
        $order = TicketOrder::where('user_id', $request->user()->getKey())
            ->where('code', $code)->with('items.visitors')->firstOrFail();

        return response()->json(['is_dummy' => true, 'currency' => 'IDR', 'order' => $order]);
    }
}
