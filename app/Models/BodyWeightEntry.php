<?php

namespace App\Models;

use App\Enums\WeightSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BodyWeightEntry extends Model
{
    protected $fillable = [
        'user_id',
        'weight_kg',
        'measured_on',
        'source',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'float',
            'measured_on' => 'date',
            'source' => WeightSource::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
