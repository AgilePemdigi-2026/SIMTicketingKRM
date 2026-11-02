<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SelectTicketPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'wisatawan';
    }

    public function rules(): array
    {
        return ['method' => ['required', 'in:qris,cash']];
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
        ];
    }
}
