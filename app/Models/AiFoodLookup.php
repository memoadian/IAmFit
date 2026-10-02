<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiFoodLookup extends Model
{
    public const STATUSES = ['pending', 'processing', 'done', 'failed'];

    protected $fillable = [
        'query',
        'query_hash',
        'status',
        'resolved_by',
        'food_id',
        'requested_by',
        'raw',
        'error',
        'prompt_tokens',
        'completion_tokens',
    ];

    protected function casts(): array
    {
        return [
            'raw' => 'array',
        ];
    }

    public static function hashFor(string $query): string
    {
        return hash('sha256', trim(mb_strtolower($query)));
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
