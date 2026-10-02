<?php

namespace App\Http\Requests;

use App\Models\FoodLogEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFoodLogRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'food_id' => ['required', 'integer', 'exists:foods,id'],
            'meal' => ['required', Rule::in(FoodLogEntry::MEALS)],
            'consumed_on' => ['nullable', 'date', 'before_or_equal:today'],
            // O bien una porción + cantidad, o bien gramos directos.
            'food_portion_id' => ['nullable', 'integer', 'exists:food_portions,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.1', 'max:50'],
            'grams' => ['required_without:food_portion_id', 'nullable', 'numeric', 'min:1', 'max:5000'],
        ];
    }
}
