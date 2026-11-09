<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Validasi dasar; integrasi master tiket dan data pengunjung belum diterapkan.
 */
class StoreTicketPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'wisatawan';
    }

    public function rules(): array
    {
        return [
            'visit_date' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:'.now('Asia/Jakarta')->toDateString(),
            ],
            'items' => ['bail', 'required', 'array', 'list', 'min:1', 'max:4'],
            'items.*' => ['bail', 'required', 'array'],
            'items.*.adult_quantity' => ['bail', 'required', 'numeric', 'integer', 'min:0', 'max:4'],
            'items.*.child_quantity' => ['bail', 'required', 'numeric', 'integer', 'min:0', 'max:4'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                // Jangan menjumlahkan input yang belum lolos validasi bentuk dan angka.
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $total = 0;

                foreach ($this->input('items') as $index => $item) {
                    $quantity = (int) $item['adult_quantity'] + (int) $item['child_quantity'];

                    if ($quantity === 0) {
                        $validator->errors()->add(
                            "items.{$index}",
                            'Setiap item harus memiliki minimal 1 tiket.'
                        );
                    }

                    $total += $quantity;
                }

                if ($total < 1 || $total > 4) {
                    $validator->errors()->add(
                        'items',
                        'Jumlah seluruh tiket dewasa dan anak dalam satu transaksi harus 1 sampai 4.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'visit_date.required' => 'Tanggal kunjungan wajib diisi.',
            'visit_date.date_format' => 'Tanggal kunjungan harus berupa tanggal valid dengan format YYYY-MM-DD.',
            'visit_date.after_or_equal' => 'Tanggal kunjungan tidak boleh sebelum hari ini menurut waktu Asia/Jakarta.',
            'items.required' => 'Daftar tiket wajib diisi.',
            'items.array' => 'Daftar tiket harus berupa array.',
            'items.list' => 'Daftar tiket harus berupa daftar berurutan.',
            'items.min' => 'Daftar tiket harus memiliki minimal 1 item.',
            'items.max' => 'Daftar tiket tidak boleh melebihi 4 item.',
            'items.*.required' => 'Item tiket wajib diisi.',
            'items.*.array' => 'Setiap item tiket harus berupa array.',
            'items.*.adult_quantity.required' => 'Jumlah tiket dewasa wajib diisi.',
            'items.*.adult_quantity.numeric' => 'Jumlah tiket dewasa harus berupa bilangan bulat.',
            'items.*.adult_quantity.integer' => 'Jumlah tiket dewasa harus berupa bilangan bulat.',
            'items.*.adult_quantity.min' => 'Jumlah tiket dewasa tidak boleh negatif.',
            'items.*.adult_quantity.max' => 'Jumlah tiket dewasa tidak boleh melebihi 4.',
            'items.*.child_quantity.required' => 'Jumlah tiket anak wajib diisi.',
            'items.*.child_quantity.numeric' => 'Jumlah tiket anak harus berupa bilangan bulat.',
            'items.*.child_quantity.integer' => 'Jumlah tiket anak harus berupa bilangan bulat.',
            'items.*.child_quantity.min' => 'Jumlah tiket anak tidak boleh negatif.',
            'items.*.child_quantity.max' => 'Jumlah tiket anak tidak boleh melebihi 4.',
        ];
    }
}
