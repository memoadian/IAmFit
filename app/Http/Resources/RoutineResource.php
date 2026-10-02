<?php

namespace App\Http\Resources;

use App\Models\Routine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Routine */
class RoutineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'notes' => $this->notes,
            'days_per_week' => $this->days_per_week,
            'is_active' => $this->is_active,
            'updated_at' => $this->updated_at,
            'days' => $this->whenLoaded('days', fn () => $this->days->map(fn ($day) => [
                'id' => $day->id,
                'label' => $day->label,
                'position' => $day->position,
                'exercises' => $day->exercises->map(fn ($re) => [
                    'id' => $re->id,
                    'exercise_id' => $re->exercise_id,
                    'exercise' => $re->relationLoaded('exercise') && $re->exercise ? [
                        'name' => $re->exercise->name,
                        'primary_muscle' => $re->exercise->primaryMuscle->name ?? null,
                        'equipment' => $re->exercise->equipment,
                    ] : null,
                    'position' => $re->position,
                    'target_sets' => $re->target_sets,
                    'target_reps_min' => $re->target_reps_min,
                    'target_reps_max' => $re->target_reps_max,
                    'target_rpe' => $re->target_rpe,
                    'rest_seconds' => $re->rest_seconds,
                    'note' => $re->note,
                ]),
            ])),
        ];
    }
}
