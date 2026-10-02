<?php

namespace App\Http\Requests;

use App\Enums\ActivityLevel;
use App\Enums\Goal;
use App\Enums\Sex;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sex' => ['required', Rule::enum(Sex::class)],
            'birthdate' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'height_cm' => ['required', 'numeric', 'min:80', 'max:260'],
            'activity_level' => ['required', Rule::enum(ActivityLevel::class)],
            'goal' => ['required', Rule::enum(Goal::class)],
            'goal_rate_kg_per_week' => ['nullable', 'numeric', 'between:-1.5,1'],
            'locale' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'timezone'],
            // Peso inicial: si no hay registros de peso todavía, se crea uno.
            'weight_kg' => ['nullable', 'numeric', 'min:25', 'max:400'],
        ];
    }
}
