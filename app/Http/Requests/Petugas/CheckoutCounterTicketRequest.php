<?php

namespace App\Http\Requests\Petugas;

use App\Http\Requests\CheckoutTicketPurchaseRequest;
use App\Models\TicketOrder;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CheckoutCounterTicketRequest extends CheckoutTicketPurchaseRequest
{
    protected $redirectRoute = 'petugas.loket.index';

    public function authorize(): bool
    {
        return $this->user()?->role === 'petugas';
    }

    public function rules(): array
    {
        $rules = parent::rules();

        // Pertahankan toleransi validasi tanggal untuk replay token
        // milik akun Wisatawan dari implementasi sebelumnya.
        $token = $this->input('checkout_token');
        $booker = $this->input('booker', []);

        if (
            is_array($booker)
            && is_string($token)
            && Str::isUuid($token)
            && isset($booker['email'], $booker['identity_number'], $booker['phone'])
        ) {
            $wisatawan = User::query()
                ->where('role', 'wisatawan')
                ->where('email', $booker['email'])
                ->where('identity_number', $booker['identity_number'])
                ->where('phone', $booker['phone'])
                ->first();

            if (
                $wisatawan !== null
                && TicketOrder::query()
                    ->where('user_id', $wisatawan->id)
                    ->where('checkout_token', $token)
                    ->exists()
            ) {
                $rules['visit_date'] = ['bail', 'required', 'date_format:Y-m-d'];
            }
        }

        // Cash maupun QRIS boleh dipilih oleh Petugas.
        $rules['payment_method'] = [
            'required',
            Rule::in(['cash', 'qris']),
        ];

        return $rules;
    }
}
