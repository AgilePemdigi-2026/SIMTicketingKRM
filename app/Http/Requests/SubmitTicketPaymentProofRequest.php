<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitTicketPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'wisatawan';
    }

    public function rules(): array
    {
        return ['proof' => ['bail', 'required', 'file', 'image', 'mimes:jpg,jpeg,png', 'extensions:jpg,jpeg,png', 'max:5120']];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $validator->errors()->has('proof')) {
                $image = @getimagesize($this->file('proof')->getRealPath());
                if (! $image || ! in_array($image[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                    $validator->errors()->add('proof', 'Bukti harus berupa gambar JPG atau PNG yang valid.');
                }
            }
        }];
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
