<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Food extends Model
{
    use SoftDeletes;

    // "food" es incontable en inglés; el inflector no lo pluraliza a "foods".
    protected $table = 'foods';

    public const SOURCES = ['off', 'usda', 'ai', 'manual'];

    protected $fillable = [
        'name', 'brand', 'barcode', 'source', 'external_id', 'locale',
        'kcal', 'protein_g', 'carb_g', 'fat_g', 'fiber_g', 'sugar_g',
        'sat_fat_g', 'sodium_mg', 'micros',
        'verified_at', 'created_by', 'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'kcal' => 'float',
            'protein_g' => 'float',
            'carb_g' => 'float',
            'fat_g' => 'float',
            'fiber_g' => 'float',
            'sugar_g' => 'float',
            'sat_fat_g' => 'float',
            'sodium_mg' => 'float',
            'micros' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function portions(): HasMany
    {
        return $this->hasMany(FoodPortion::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /** Macros/kcal para una cantidad dada en gramos (los datos son por 100 g). */
    public function nutrientsForGrams(float $grams): array
    {
        $factor = $grams / 100;

        return [
            'kcal' => round($this->kcal * $factor, 2),
            'protein_g' => round($this->protein_g * $factor, 2),
            'carb_g' => round($this->carb_g * $factor, 2),
            'fat_g' => round($this->fat_g * $factor, 2),
        ];
    }

    /** Búsqueda tolerante a acentos/typos usando el índice trigram de Postgres. */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim(mb_strtolower($term));

        return $query
            ->where(function (Builder $q) use ($term) {
                $q->whereRaw('lower(name) % ?', [$term])
                    ->orWhereRaw('lower(name) like ?', ['%'.$term.'%']);
            })
            ->orderByRaw('similarity(lower(name), ?) desc', [$term]);
    }
}
