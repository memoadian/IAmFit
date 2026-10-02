<?php

namespace App\Http\Requests;

use App\Enums\MealType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFoodLogRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'food_id' => ['required', 'integer', 'exists:foods,id'],
            'meal' => ['required', Rule::enum(MealType::class)],
            'consumed_on' => ['nullable', 'date', 'before_or_equal:tomorrow'],
            'timezone' => ['nullable', 'timezone'],
            // O bien una porción + cantidad, o bien gramos directos.
            'food_portion_id' => ['nullable', 'integer', 'exists:food_portions,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.1', 'max:50'],
            'grams' => ['required_without:food_portion_id', 'nullable', 'numeric', 'min:1', 'max:5000'],
        ];
    }
}
