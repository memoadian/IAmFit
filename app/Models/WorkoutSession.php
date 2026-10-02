<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSession extends Model
{
    protected $fillable = [
        'user_id',
        'routine_day_id',
        'performed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return ['performed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function routineDay(): BelongsTo
    {
        return $this->belongsTo(RoutineDay::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(SetLog::class)->orderBy('set_number');
    }
}
