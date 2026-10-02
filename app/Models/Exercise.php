<?php

namespace App\Models;

use App\Enums\Equipment;
use App\Enums\Mechanic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exercise extends Model
{
    protected $fillable = [
        'slug', 'name', 'description', 'primary_muscle_id',
        'equipment', 'mechanic', 'created_by', 'is_public',
    ];

    protected function casts(): array
    {
        return [
            'equipment' => Equipment::class,
            'mechanic' => Mechanic::class,
            'is_public' => 'boolean',
        ];
    }

    public function primaryMuscle(): BelongsTo
    {
        return $this->belongsTo(Muscle::class, 'primary_muscle_id');
    }

    public function secondaryMuscles(): BelongsToMany
    {
        return $this->belongsToMany(Muscle::class, 'exercise_secondary_muscle');
    }

    /** Visible para $user: catálogo público o creado por él. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('is_public', true)->orWhere('created_by', $user->id);
        });
    }
}
