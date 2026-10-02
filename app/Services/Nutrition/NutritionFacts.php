<?php

namespace App\Services\Nutrition;

/**
 * Datos nutricionales normalizados que devuelve cualquier NutritionSource.
 * Todos los valores son POR 100 g de porción comestible.
 */
final class NutritionFacts
{
    /**
     * @param  array<int, array{label: string, grams: float}>  $portions
     * @param  array<string, mixed>  $micros
     * @param  array<string, mixed>  $raw  Respuesta cruda de la fuente, para depurar.
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $brand,
        public readonly float $kcal,
        public readonly float $proteinG,
        public readonly float $carbG,
        public readonly float $fatG,
        public readonly ?float $fiberG = null,
        public readonly ?float $sugarG = null,
        public readonly ?float $satFatG = null,
        public readonly ?float $sodiumMg = null,
        public readonly ?string $barcode = null,
        public readonly ?string $externalId = null,
        public readonly ?string $locale = null,
        public readonly array $portions = [],
        public readonly array $micros = [],
        public readonly array $raw = [],
    ) {}

    /** @return array<string, mixed> */
    public function toFoodAttributes(string $source): array
    {
        return [
            'name' => $this->name,
            'brand' => $this->brand,
            'barcode' => $this->barcode,
            'source' => $source,
            'external_id' => $this->externalId,
            'locale' => $this->locale,
            'kcal' => round($this->kcal, 2),
            'protein_g' => round($this->proteinG, 2),
            'carb_g' => round($this->carbG, 2),
            'fat_g' => round($this->fatG, 2),
            'fiber_g' => $this->fiberG !== null ? round($this->fiberG, 2) : null,
            'sugar_g' => $this->sugarG !== null ? round($this->sugarG, 2) : null,
            'sat_fat_g' => $this->satFatG !== null ? round($this->satFatG, 2) : null,
            'sodium_mg' => $this->sodiumMg !== null ? round($this->sodiumMg, 2) : null,
            'micros' => $this->micros ?: null,
        ];
    }

    public function looksValid(): bool
    {
        return $this->name !== ''
            && $this->kcal >= 0 && $this->kcal <= 1000
            && $this->proteinG >= 0 && $this->carbG >= 0 && $this->fatG >= 0;
    }
}
