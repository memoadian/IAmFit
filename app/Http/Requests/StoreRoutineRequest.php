<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoutineRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'days_per_week' => ['nullable', 'integer', 'between:1,7'],
            'is_active' => ['nullable', 'boolean'],

            'days' => ['required', 'array', 'min:1', 'max:7'],
            'days.*.label' => ['required', 'string', 'max:80'],
            'days.*.exercises' => ['required', 'array', 'min:1', 'max:20'],
            'days.*.exercises.*.exercise_id' => ['required', 'integer', 'exists:exercises,id'],
            'days.*.exercises.*.target_sets' => ['nullable', 'integer', 'between:1,15'],
            'days.*.exercises.*.target_reps_min' => ['nullable', 'integer', 'between:1,50'],
            'days.*.exercises.*.target_reps_max' => ['nullable', 'integer', 'between:1,50', 'gte:days.*.exercises.*.target_reps_min'],
            'days.*.exercises.*.target_rpe' => ['nullable', 'numeric', 'between:5,10'],
            'days.*.exercises.*.rest_seconds' => ['nullable', 'integer', 'between:0,600'],
            'days.*.exercises.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
