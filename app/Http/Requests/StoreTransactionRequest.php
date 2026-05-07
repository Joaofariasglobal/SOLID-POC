<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Rules\ValidCategoryForTypeRule;

class StoreTransactionRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => (int) $this->route('id'),
        ]);
    }
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'type' => 'required|string|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'category' => ['required', 'string', new ValidCategoryForTypeRule($this->input('type'))
            ],
            'description' => 'nullable|string|max:255',
            'currency' => 'nullable|string|in:BRL,USD,EUR',
            'discount' => 'nullable|numeric|between:0,100',
            'occurred_at' => 'nullable|date',
        ];
    }
    public function messages(): array
    {
        return [
            'type.required' => 'O campo type é obrigatório.',
            'type.in' => 'O campo type deve ser "income" ou "expense".',
            'amount.required' => 'O campo amount é obrigatório.',
            'amount.numeric' => 'O campo amount deve ser um número.',
            'amount.min' => 'O campo amount deve ser maior que zero.',
            'category.required' => 'O campo category é obrigatório.',
            'currency.in' => 'O campo currency deve ser "BRL", "USD" ou "EUR".',
            'discount.between' => 'O campo discount deve estar entre 0 e 100.',
        ];
    }
}