<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Routine extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'notes',
        'days_per_week',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'days_per_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(RoutineDay::class)->orderBy('position');
    }
}
