<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TicketPayment;

class ReviewTicketPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'petugas';
    }

    public function rules(): array
    {
        $rules = [
            'submission_id' => ['nullable', 'integer', 'min:1'],
            'merchant_reference' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9._:\\/-]+$/',
            ],
            'reason' => ['nullable', 'string', 'max:1000'],
            'receipt_checked' => ['required', 'accepted'],
        ];

        $payment = $this->route('payment');

        // Nominal uang diterima hanya diwajibkan
        // ketika Petugas mengonfirmasi Cash pembelian loket.
        if (
            $this->routeIs('ticket-payment.staff.confirm')
            && $payment instanceof TicketPayment
            && $payment->method === 'cash'
            && $payment->order?->sale_channel === 'counter'
        ) {
            $rules['cash_received_amount'] = [
                'required',
                'integer',
                'min:0',
                'max:999999999999',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'method.required' => 'Pilih metode pembayaran.',
            'method.in' => 'Metode pembayaran harus QRIS atau cash.',
            'proof.required' => 'Unggah bukti pembayaran.',
            'proof.image' => 'Bukti harus berupa gambar JPG atau PNG.',
            'proof.mimes' => 'Bukti harus berupa JPG atau PNG.',
            'proof.extensions' => 'Ekstensi bukti harus JPG atau PNG.',
            'proof.max' => 'Ukuran bukti maksimal 5 MB.',
            'receipt_checked.required' => 'Konfirmasikan bahwa penerimaan pembayaran sudah diperiksa.',
            'receipt_checked.accepted' => 'Konfirmasikan bahwa penerimaan pembayaran sudah diperiksa.',
            'merchant_reference.regex' => 'Referensi hanya boleh berisi huruf, angka, titik, garis miring, titik dua, garis bawah, atau tanda hubung.',
            'merchant_reference.max' => 'Referensi maksimal 100 karakter.',
            'reason.max' => 'Alasan maksimal 1000 karakter.',
            'cash_received_amount.required' => 'Nominal uang diterima wajib diisi.',
            'cash_received_amount.integer' => 'Nominal uang diterima harus berupa angka rupiah bulat.',
            'cash_received_amount.min' => 'Nominal uang diterima tidak boleh negatif.',
        ];
    }
}
