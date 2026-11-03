<?php

namespace App\Http\Controllers\Wisatawan;

use App\Contracts\TicketCatalogProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutTicketPurchaseRequest;
use App\Http\Requests\PreviewTicketPurchaseRequest;
use App\Models\TicketOrder;
use App\Services\TicketPurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TicketPurchaseController extends Controller
{
    public function index(Request $request, TicketCatalogProvider $catalog): JsonResponse|View
    {
        $tickets = $catalog->activeTickets();
        if (! $request->expectsJson()) {
            return view('wisatawan.ticket-purchase.index', [
                'tickets' => $tickets,
                'booker' => $request->user()->only(['name', 'identity_number', 'phone', 'email']),
                'visitorType' => $request->user()->visitor_type === 'mancanegara' ? 'mancanegara' : 'domestik',
                'today' => now('Asia/Jakarta')->toDateString(),
                'checkoutToken' => is_string($request->old('checkout_token')) && Str::isUuid($request->old('checkout_token'))
                    ? $request->old('checkout_token') : (string) Str::uuid(),
            ]);
        }

        return response()->json(['is_dummy' => false, 'currency' => 'IDR', 'tickets' => $tickets]);
    }

    public function preview(PreviewTicketPurchaseRequest $request, TicketPurchaseService $service): JsonResponse
    {
        return response()->json([
            'is_dummy' => false, 'currency' => 'IDR',
            'quote' => $service->calculate($request->validated()),
            'quota_reserved' => false,
        ]);
    }

    public function orders(Request $request): JsonResponse|View
    {
        $orders = TicketOrder::with(['payment', 'items'])
            ->where('user_id', $request->user()->getKey())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);
        if ($request->expectsJson()) {
            return response()->json(['is_dummy' => false, 'currency' => 'IDR', 'orders' => $orders]);
        }

        return view('wisatawan.ticket-purchase.orders', compact('orders'));
    }

    public function store(CheckoutTicketPurchaseRequest $request, TicketPurchaseService $service): JsonResponse|RedirectResponse
    {
        $order = $service->create($request->user(), $request->validated());

        if (! $request->expectsJson()) {
            return redirect()->route('ticket-purchase.show', $order->code)->with('purchase_created', true);
        }

        return response()->json(['is_dummy' => false, 'currency' => 'IDR', 'order' => $order], 201);
    }

    public function show(Request $request, string $code): JsonResponse|View
    {
        $order = TicketOrder::where('user_id', $request->user()->getKey())
            ->where('code', $code)->with(['items.visitors', 'payment'])->firstOrFail();

        if (! $request->expectsJson()) {
            return view('wisatawan.ticket-purchase.show', compact('order'));
        }

        return response()->json(['is_dummy' => false, 'currency' => 'IDR', 'order' => $order]);
    }
}
