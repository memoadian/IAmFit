<?php

namespace App\Models;

use App\Enums\MuscleGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Muscle extends Model
{
    protected $fillable = ['slug', 'name', 'group'];

    protected function casts(): array
    {
        return ['group' => MuscleGroup::class];
    }

    public function primaryExercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'primary_muscle_id');
    }

    public function exercisesAsSecondary(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'exercise_secondary_muscle');
    }
}
