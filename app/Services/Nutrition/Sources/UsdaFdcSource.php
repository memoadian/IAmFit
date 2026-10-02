<?php

namespace App\Services\Nutrition\Sources;

use App\Services\Nutrition\Contracts\NutritionSource;
use App\Services\Nutrition\NutritionFacts;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * USDA FoodData Central: base oficial de EE. UU., muy completa para alimentos
 * genéricos ("pollo, pechuga, cocido"). Requiere API key gratuita.
 *
 * Números de nutriente FDC:
 *   1008 Energía (kcal) · 1003 Proteína · 1005 Carbohidratos · 1004 Grasa total
 *   1079 Fibra · 2000 Azúcares · 1258 Grasa saturada · 1093 Sodio (mg)
 */
class UsdaFdcSource implements NutritionSource
{
    private const NUTRIENT = [
        'kcal' => 1008,
        'protein' => 1003,
        'carb' => 1005,
        'fat' => 1004,
        'fiber' => 1079,
        'sugar' => 2000,
        'sat_fat' => 1258,
        'sodium' => 1093,
    ];

    public function key(): string
    {
        return 'usda';
    }

    public function isEnabled(): bool
    {
        return (bool) config('services.usda.api_key');
    }

    public function search(string $query): ?NutritionFacts
    {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            $response = $this->client()->get('/foods/search', [
                'query' => $query,
                'pageSize' => 5,
                'dataType' => 'Foundation,SR Legacy,Branded',
            ]);
        } catch (Throwable $e) {
            Log::warning('usda.search_failed', ['query' => $query, 'error' => $e->getMessage()]);

            return null;
        }

        foreach ($response->json('foods', []) as $food) {
            $facts = $this->mapFood($food);
            if ($facts?->looksValid()) {
                return $facts;
            }
        }

        return null;
    }

    public function byBarcode(string $barcode): ?NutritionFacts
    {
        // FDC indexa el UPC como parte del texto; se resuelve vía search().
        return $this->isEnabled() ? $this->search($barcode) : null;
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(config('services.usda.base_url'))
            ->withQueryParameters(['api_key' => config('services.usda.api_key')])
            ->acceptJson()
            ->timeout(8);
    }

    /** @param array<string, mixed> $food */
    private function mapFood(array $food): ?NutritionFacts
    {
        $name = trim((string) ($food['description'] ?? ''));
        if ($name === '') {
            return null;
        }

        $byNumber = [];
        foreach ($food['foodNutrients'] ?? [] as $fn) {
            $number = $fn['nutrientNumber'] ?? ($fn['nutrient']['number'] ?? null);
            $value = $fn['value'] ?? ($fn['amount'] ?? null);
            if ($number !== null && $value !== null) {
                $byNumber[(int) $number] = (float) $value;
            }
        }

        if (! isset($byNumber[self::NUTRIENT['kcal']])) {
            return null;
        }

        return new NutritionFacts(
            name: ucfirst(mb_strtolower($name)),
            brand: $food['brandOwner'] ?? ($food['brandName'] ?? null),
            kcal: $byNumber[self::NUTRIENT['kcal']],
            proteinG: $byNumber[self::NUTRIENT['protein']] ?? 0,
            carbG: $byNumber[self::NUTRIENT['carb']] ?? 0,
            fatG: $byNumber[self::NUTRIENT['fat']] ?? 0,
            fiberG: $byNumber[self::NUTRIENT['fiber']] ?? null,
            sugarG: $byNumber[self::NUTRIENT['sugar']] ?? null,
            satFatG: $byNumber[self::NUTRIENT['sat_fat']] ?? null,
            sodiumMg: $byNumber[self::NUTRIENT['sodium']] ?? null,
            barcode: $food['gtinUpc'] ?? null,
            externalId: isset($food['fdcId']) ? (string) $food['fdcId'] : null,
            locale: 'en-US',
            raw: $food,
        );
    }
}
