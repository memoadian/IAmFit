<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    public const SEXES = ['male', 'female'];

    public const ACTIVITY_LEVELS = ['sedentary', 'light', 'moderate', 'active', 'very_active'];

    public const GOALS = ['lose', 'maintain', 'gain'];

    /** Multiplicadores sobre el BMR para estimar el TDEE (gasto total diario). */
    public const ACTIVITY_FACTORS = [
        'sedentary' => 1.2,
        'light' => 1.375,
        'moderate' => 1.55,
        'active' => 1.725,
        'very_active' => 1.9,
    ];

    protected $fillable = [
        'sex',
        'birthdate',
        'height_cm',
        'activity_level',
        'goal',
        'goal_rate_kg_per_week',
        'locale',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'height_cm' => 'float',
            'goal_rate_kg_per_week' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ageYears(): int
    {
        return (int) $this->birthdate->diffInYears(now());
    }

    public function activityFactor(): float
    {
        return self::ACTIVITY_FACTORS[$this->activity_level] ?? 1.55;
    }
}
