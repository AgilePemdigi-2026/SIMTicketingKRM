<?php

namespace App\Http\Requests\Petugas;

use App\Http\Requests\PreviewTicketPurchaseRequest;
use Illuminate\Validation\Rule;

class PreviewCounterTicketRequest extends PreviewTicketPurchaseRequest
{
    protected $redirectRoute = 'petugas.loket.index';

    public function authorize(): bool
    {
        return $this->user()?->role === 'petugas';
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'payment_method' => [
                'required',
                Rule::in(['cash', 'qris']),
            ],
        ]);
    }
}
