<?php

namespace App\Http\Requests;

use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sex' => ['required', Rule::in(Profile::SEXES)],
            'birthdate' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'height_cm' => ['required', 'numeric', 'min:80', 'max:260'],
            'activity_level' => ['required', Rule::in(Profile::ACTIVITY_LEVELS)],
            'goal' => ['required', Rule::in(Profile::GOALS)],
            'goal_rate_kg_per_week' => ['nullable', 'numeric', 'between:-1.5,1'],
            'locale' => ['nullable', 'string', 'max:10'],
            // Peso inicial: si no hay registros de peso todavía, se crea uno.
            'weight_kg' => ['nullable', 'numeric', 'min:25', 'max:400'],
        ];
    }
}
