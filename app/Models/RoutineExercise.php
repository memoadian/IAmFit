<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineExercise extends Model
{
    protected $fillable = [
        'routine_day_id',
        'exercise_id',
        'position',
        'target_sets',
        'target_reps_min',
        'target_reps_max',
        'target_rpe',
        'rest_seconds',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'target_sets' => 'integer',
            'target_reps_min' => 'integer',
            'target_reps_max' => 'integer',
            'target_rpe' => 'float',
            'rest_seconds' => 'integer',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(RoutineDay::class, 'routine_day_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
