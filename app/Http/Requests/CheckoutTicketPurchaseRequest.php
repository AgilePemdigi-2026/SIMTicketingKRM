<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CheckoutTicketPurchaseRequest extends StoreTicketPurchaseRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'visitor_type' => ['required', Rule::in(['domestik', 'mancanegara'])],
            'booker' => ['required', 'array'],
            'booker.name' => ['required', 'string', 'max:255'],
            'booker.identity_number' => ['required', 'string', 'max:50'],
            'booker.phone' => ['required', 'string', 'max:25', 'regex:/^\+?[0-9][0-9 ()-]{5,24}$/'],
            'booker.email' => ['required', 'email', 'max:255'],
            'items.*.ticket_reference' => ['required', 'string', 'max:64'],
            'items.*.visitors' => ['required', 'array', 'list', 'min:1', 'max:4'],
            'items.*.visitors.*' => ['required', 'array'],
            'items.*.visitors.*.name' => ['required', 'string', 'max:255'],
            'items.*.visitors.*.identity_number' => ['required', 'string', 'max:50'],
            'items.*.visitors.*.age_category' => ['required', Rule::in(['adult', 'child'])],
        ]);
    }

    public function after(): array
    {
        return [
            ...parent::after(),
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                foreach ($this->input('items') as $index => $item) {
                    $visitors = collect($item['visitors']);
                    if ($visitors->where('age_category', 'adult')->count() !== (int) $item['adult_quantity']
                        || $visitors->where('age_category', 'child')->count() !== (int) $item['child_quantity']) {
                        $validator->errors()->add(
                            "items.{$index}.visitors",
                            'Jumlah pengunjung dewasa dan anak harus sesuai dengan jumlah tiket pada item tersebut.'
                        );
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'array' => ':attribute harus berupa array.',
            'list' => ':attribute harus berupa daftar berurutan.',
            'in' => 'Pilihan :attribute tidak valid.',
            'max' => ':attribute melebihi batas :max.',
            'min' => ':attribute harus berisi minimal :min.',
            'booker.email.email' => 'Email pemesan tidak valid.',
            'booker.phone.regex' => 'Nomor telepon pemesan tidak valid.',
        ]);
    }

    public function attributes(): array
    {
        return [
            'visitor_type' => 'jenis wisatawan',
            'booker' => 'identitas pemesan',
            'booker.name' => 'nama pemesan',
            'booker.identity_number' => 'nomor identitas pemesan',
            'booker.phone' => 'nomor telepon pemesan',
            'booker.email' => 'email pemesan',
            'items.*.ticket_reference' => 'referensi tiket',
            'items.*.visitors' => 'daftar pengunjung',
            'items.*.visitors.*' => 'data pengunjung',
            'items.*.visitors.*.name' => 'nama pengunjung',
            'items.*.visitors.*.identity_number' => 'nomor identitas pengunjung',
            'items.*.visitors.*.age_category' => 'kategori usia pengunjung',
        ];
    }
}
