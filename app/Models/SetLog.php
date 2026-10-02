<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetLog extends Model
{
    protected $fillable = [
        'workout_session_id',
        'exercise_id',
        'set_number',
        'weight_kg',
        'reps',
        'rpe',
        'is_warmup',
    ];

    protected function casts(): array
    {
        return [
            'set_number' => 'integer',
            'weight_kg' => 'float',
            'reps' => 'integer',
            'rpe' => 'float',
            'is_warmup' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class, 'workout_session_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /** Estimación de 1RM (Epley) — útil para el consejo de carga de la IA. */
    public function estimatedOneRepMax(): float
    {
        return round($this->weight_kg * (1 + $this->reps / 30), 1);
    }
}
