<?php

namespace App\Services\Nutrition;

use App\Services\Nutrition\Contracts\NutritionSource;
use Illuminate\Support\Facades\Log;

/**
 * Prueba las fuentes de datos nutricionales en orden (config/nutrition.php) y
 * devuelve el primer resultado válido, junto con la clave de la fuente que lo
 * resolvió.
 */
class NutritionLookup
{
    /** @var array<int, NutritionSource> */
    private array $sources;

    public function __construct()
    {
        $this->sources = array_map(
            fn (string $class) => app($class),
            config('nutrition.sources', []),
        );
    }

    /**
     * @return array{source: string, facts: NutritionFacts}|null
     */
    public function search(string $query): ?array
    {
        return $this->run(fn (NutritionSource $s) => $s->search($query), $query);
    }

    /**
     * @return array{source: string, facts: NutritionFacts}|null
     */
    public function byBarcode(string $barcode): ?array
    {
        return $this->run(fn (NutritionSource $s) => $s->byBarcode($barcode), $barcode);
    }

    private function run(callable $call, string $term): ?array
    {
        foreach ($this->sources as $source) {
            if (! $source->isEnabled()) {
                continue;
            }

            $facts = $call($source);

            if ($facts !== null && $facts->looksValid()) {
                Log::info('nutrition_lookup.hit', ['source' => $source->key(), 'term' => $term]);

                return ['source' => $source->key(), 'facts' => $facts];
            }
        }

        Log::info('nutrition_lookup.miss', ['term' => $term]);

        return null;
    }
}
