<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class PreviewTicketPurchaseRequest extends StoreTicketPurchaseRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'visitor_type' => ['required', Rule::in(['domestik', 'mancanegara'])],
            'items.*.ticket_reference' => ['required', 'string', 'max:64'],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'visitor_type.required' => 'Jenis wisatawan wajib dipilih.',
            'visitor_type.in' => 'Jenis wisatawan tidak valid.',
            'items.*.ticket_reference.required' => 'Pilihan tiket wajib diisi.',
            'items.*.ticket_reference.string' => 'Referensi tiket tidak valid.',
            'items.*.ticket_reference.max' => 'Referensi tiket tidak valid.',
        ]);
    }
}
