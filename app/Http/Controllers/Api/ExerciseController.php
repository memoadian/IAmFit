<?php

namespace App\Http\Controllers\Api;

use App\Enums\Equipment;
use App\Enums\MuscleGroup;
use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'muscle' => ['nullable', 'string', 'exists:muscles,slug'],
            'group' => ['nullable', Rule::enum(MuscleGroup::class)],
            'equipment' => ['nullable', Rule::enum(Equipment::class)],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $exercises = Exercise::query()
            ->visibleTo($request->user())
            ->with('primaryMuscle:id,slug,name,group', 'secondaryMuscles:id,slug,name')
            ->when($data['muscle'] ?? null, fn ($q, $slug) => $q->whereHas(
                'primaryMuscle', fn ($m) => $m->where('slug', $slug)
            ))
            ->when($data['group'] ?? null, fn ($q, $group) => $q->whereHas(
                'primaryMuscle', fn ($m) => $m->where('group', $group)
            ))
            ->when($data['equipment'] ?? null, fn ($q, $eq) => $q->where('equipment', $eq))
            ->when($data['q'] ?? null, fn ($q, $term) => $q->whereRaw('name ilike ?', ['%'.$term.'%']))
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $exercises]);
    }
}
