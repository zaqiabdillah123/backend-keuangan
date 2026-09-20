<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'budget_id'        => ['required', 'integer', 'exists:budgets,id'],
            'category_id'      => ['required', 'integer', 'exists:categories,id'],
            'item_name'        => ['required', 'string', 'max:255'],
            'quantity'         => ['required', 'integer', 'min:1'],
            'unit_price'       => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'budget_id.required'   => 'ID Anggaran wajib diisi.',
            'category_id.required' => 'Kategori pengeluaran wajib dipilih.',
            'item_name.required'   => 'Nama barang wajib diisi.',
            'unit_price.min'       => 'Harga barang harus lebih besar dari 0.',
        ];
    }
}