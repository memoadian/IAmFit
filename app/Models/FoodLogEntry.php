<?php

namespace App\Models;

use App\Enums\MealType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodLogEntry extends Model
{
    protected $fillable = [
        'user_id',
        'food_id',
        'food_portion_id',
        'meal',
        'consumed_on',
        'quantity',
        'grams',
        'kcal',
        'protein_g',
        'carb_g',
        'fat_g',
    ];

    protected function casts(): array
    {
        return [
            'consumed_on' => 'date',
            'meal' => MealType::class,
            'quantity' => 'float',
            'grams' => 'float',
            'kcal' => 'float',
            'protein_g' => 'float',
            'carb_g' => 'float',
            'fat_g' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function portion(): BelongsTo
    {
        return $this->belongsTo(FoodPortion::class, 'food_portion_id');
    }
}
