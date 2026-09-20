<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'budget_id' => ['required', 'integer', 'exists:budgets,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'budget_id.required' => 'ID Anggaran wajib diisi.',
            'category_id.required' => 'Kategori pemasukan wajib dipilih.',
            'item_name.required' => 'Sumber pemasukan wajib diisi.',
            'unit_price.min' => 'Nominal pemasukan harus lebih besar dari 0.',
        ];
    }
}