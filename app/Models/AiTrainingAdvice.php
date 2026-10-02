<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiTrainingAdvice extends Model
{
    protected $fillable = [
        'user_id',
        'routine_id',
        'context',
        'advice',
        'provider',
        'prompt_tokens',
        'completion_tokens',
    ];

    protected function casts(): array
    {
        return ['context' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }
}
