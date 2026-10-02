<?php

namespace App\Models;

use App\Enums\ActivityLevel;
use App\Enums\Goal;
use App\Enums\Sex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'sex',
        'birthdate',
        'height_cm',
        'activity_level',
        'goal',
        'goal_rate_kg_per_week',
        'locale',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'sex' => Sex::class,
            'birthdate' => 'date',
            'height_cm' => 'float',
            'activity_level' => ActivityLevel::class,
            'goal' => Goal::class,
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
        return $this->activity_level->factor();
    }
}
