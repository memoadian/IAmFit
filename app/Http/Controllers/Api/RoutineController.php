<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoutineRequest;
use App\Http\Resources\RoutineResource;
use App\Models\Routine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoutineController extends Controller
{
    public function index(Request $request)
    {
        $routines = $request->user()->routines()
            ->withCount('days')
            ->latest()
            ->get();

        return RoutineResource::collection($routines);
    }

    public function store(StoreRoutineRequest $request)
    {
        $routine = DB::transaction(function () use ($request) {
            $routine = $request->user()->routines()->create([
                'name' => $request->string('name'),
                'notes' => $request->input('notes'),
                'days_per_week' => $request->integer('days_per_week') ?: count($request->input('days')),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->syncDays($routine, $request->validated('days'));

            return $routine;
        });

        return (new RoutineResource($this->loadTree($routine)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Routine $routine)
    {
        $this->authorizeRoutine($request, $routine);

        return new RoutineResource($this->loadTree($routine));
    }

    public function update(StoreRoutineRequest $request, Routine $routine)
    {
        $this->authorizeRoutine($request, $routine);

        DB::transaction(function () use ($request, $routine) {
            $routine->update([
                'name' => $request->string('name'),
                'notes' => $request->input('notes'),
                'days_per_week' => $request->integer('days_per_week') ?: count($request->input('days')),
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Sync destructivo: se reemplaza el árbol de días/ejercicios completo.
            $routine->days()->delete();
            $this->syncDays($routine, $request->validated('days'));
        });

        return new RoutineResource($this->loadTree($routine->fresh()));
    }

    public function destroy(Request $request, Routine $routine)
    {
        $this->authorizeRoutine($request, $routine);

        $routine->delete();

        return response()->noContent();
    }

    private function authorizeRoutine(Request $request, Routine $routine): void
    {
        abort_unless($routine->user_id === $request->user()->id, 403);
    }

    /** @param array<int, array<string, mixed>> $days */
    private function syncDays(Routine $routine, array $days): void
    {
        foreach (array_values($days) as $dayIndex => $day) {
            $routineDay = $routine->days()->create([
                'label' => $day['label'],
                'position' => $dayIndex + 1,
            ]);

            foreach (array_values($day['exercises']) as $exIndex => $ex) {
                $routineDay->exercises()->create([
                    'exercise_id' => $ex['exercise_id'],
                    'position' => $exIndex + 1,
                    'target_sets' => $ex['target_sets'] ?? 3,
                    'target_reps_min' => $ex['target_reps_min'] ?? 8,
                    'target_reps_max' => $ex['target_reps_max'] ?? 12,
                    'target_rpe' => $ex['target_rpe'] ?? null,
                    'rest_seconds' => $ex['rest_seconds'] ?? null,
                    'note' => $ex['note'] ?? null,
                ]);
            }
        }
    }

    private function loadTree(Routine $routine): Routine
    {
        return $routine->load('days.exercises.exercise.primaryMuscle');
    }
}
