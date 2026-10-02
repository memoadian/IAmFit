<?php

namespace App\Services\Nutrition\Sources;

use App\Services\Nutrition\Contracts\NutritionSource;
use App\Services\Nutrition\NutritionFacts;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Open Food Facts: base abierta y colaborativa con fuerte presencia de productos
 * mexicanos (empaquetados, con código de barras). Sin API key; sólo exige un
 * User-Agent identificable.
 */
class OpenFoodFactsSource implements NutritionSource
{
    public function key(): string
    {
        return 'off';
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function search(string $query): ?NutritionFacts
    {
        try {
            $response = Http::baseUrl(config('services.openfoodfacts.search_url'))
                ->withUserAgent(config('services.openfoodfacts.user_agent'))
                ->acceptJson()
                ->timeout(10)
                ->get('/search', [
                    'q' => $query,
                    'fields' => 'code,product_name,brands,nutriments,lang',
                    'page_size' => 5,
                ]);
        } catch (Throwable $e) {
            Log::warning('off.search_failed', ['query' => $query, 'error' => $e->getMessage()]);

            return null;
        }

        foreach ($response->json('hits', []) as $product) {
            $facts = $this->mapProduct($product);
            if ($facts?->looksValid()) {
                return $facts;
            }
        }

        return null;
    }

    public function byBarcode(string $barcode): ?NutritionFacts
    {
        try {
            $response = Http::baseUrl(config('services.openfoodfacts.base_url'))
                ->withUserAgent(config('services.openfoodfacts.user_agent'))
                ->acceptJson()
                ->timeout(8)
                ->get("/api/v2/product/{$barcode}", [
                    'fields' => 'code,product_name,brands,nutriments,countries,lang',
                ]);
        } catch (Throwable $e) {
            Log::warning('off.barcode_failed', ['barcode' => $barcode, 'error' => $e->getMessage()]);

            return null;
        }

        if ($response->json('status') !== 1) {
            return null;
        }

        return $this->mapProduct($response->json('product', []));
    }

    /** @param array<string, mixed> $product */
    private function mapProduct(array $product): ?NutritionFacts
    {
        $name = trim((string) ($product['product_name'] ?? $product['product_name_es'] ?? ''));
        $n = $product['nutriments'] ?? [];

        $kcal = $this->kcalPer100g($n);

        if ($name === '' || $kcal === null) {
            return null;
        }

        $sodiumMg = isset($n['sodium_100g'])
            ? ((float) $n['sodium_100g']) * 1000
            : (isset($n['salt_100g']) ? ((float) $n['salt_100g']) * 400 : null);

        return new NutritionFacts(
            name: $name,
            brand: $this->firstBrand($product['brands'] ?? null),
            kcal: (float) $kcal,
            proteinG: (float) ($n['proteins_100g'] ?? 0),
            carbG: (float) ($n['carbohydrates_100g'] ?? 0),
            fatG: (float) ($n['fat_100g'] ?? 0),
            fiberG: isset($n['fiber_100g']) ? (float) $n['fiber_100g'] : null,
            sugarG: isset($n['sugars_100g']) ? (float) $n['sugars_100g'] : null,
            satFatG: isset($n['saturated-fat_100g']) ? (float) $n['saturated-fat_100g'] : null,
            sodiumMg: $sodiumMg,
            barcode: $product['code'] ?? null,
            externalId: $product['code'] ?? null,
            locale: is_string($product['lang'] ?? null) ? $product['lang'] : null,
            raw: $product,
        );
    }

    /**
     * OFF no siempre trae "energy-kcal_100g"; a veces sólo kJ o "energy_100g"
     * con unidad aparte.
     *
     * @param  array<string, mixed>  $n
     */
    private function kcalPer100g(array $n): ?float
    {
        if (isset($n['energy-kcal_100g'])) {
            return (float) $n['energy-kcal_100g'];
        }

        if (isset($n['energy-kj_100g'])) {
            return round(((float) $n['energy-kj_100g']) / 4.184, 1);
        }

        if (isset($n['energy_100g'])) {
            $value = (float) $n['energy_100g'];
            $unit = strtolower((string) ($n['energy_unit'] ?? 'kj'));

            return $unit === 'kcal' ? $value : round($value / 4.184, 1);
        }

        return null;
    }

    /** OFF devuelve "brands" como string (API de producto) o array (buscador). */
    private function firstBrand(string|array|null $brands): ?string
    {
        if (is_array($brands)) {
            $brands = $brands[0] ?? null;
        }

        if (! is_string($brands) || $brands === '') {
            return null;
        }

        return trim(explode(',', $brands)[0]) ?: null;
    }
}
