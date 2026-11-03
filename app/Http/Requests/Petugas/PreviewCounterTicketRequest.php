<?php

namespace App\Http\Requests\Petugas;

use App\Http\Requests\PreviewTicketPurchaseRequest;

class PreviewCounterTicketRequest extends PreviewTicketPurchaseRequest
{
    protected $redirectRoute = 'petugas.loket.index';

    public function authorize(): bool
    {
        return $this->user()?->role === 'petugas';
    }
}
