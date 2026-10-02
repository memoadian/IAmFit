<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutineDay extends Model
{
    protected $fillable = [
        'routine_id',
        'label',
        'position',
    ];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(RoutineExercise::class)->orderBy('position');
    }
}
