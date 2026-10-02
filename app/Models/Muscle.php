<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Muscle extends Model
{
    public const GROUPS = ['chest', 'back', 'shoulders', 'arms', 'legs', 'core'];

    protected $fillable = ['slug', 'name', 'group'];

    public function primaryExercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'primary_muscle_id');
    }

    public function exercisesAsSecondary(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'exercise_secondary_muscle');
    }
}
