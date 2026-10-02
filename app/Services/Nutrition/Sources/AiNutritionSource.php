<?php

namespace App\Services\Nutrition\Sources;

use App\Contracts\AiChatProvider;
use App\Exceptions\AiException;
use App\Services\Nutrition\Contracts\NutritionSource;
use App\Services\Nutrition\NutritionFacts;
use Illuminate\Support\Facades\Log;

/**
 * Último recurso: la IA estima los datos nutricionales de un platillo que no
 * está en ninguna base abierta (típico en cocina mexicana: "tlacoyo de haba",
 * "cochinita pibil"). Lo que devuelve se guarda SIN verificar y la app lo marca
 * como estimado hasta que un admin lo revisa.
 */
class AiNutritionSource implements NutritionSource
{
    public function __construct(private readonly AiChatProvider $ai) {}

    public function key(): string
    {
        return 'ai';
    }

    public function isEnabled(): bool
    {
        return (bool) config('services.groq.api_key');
    }

    public function byBarcode(string $barcode): ?NutritionFacts
    {
        // La IA no puede resolver códigos de barras de forma fiable.
        return null;
    }

    public function search(string $query): ?NutritionFacts
    {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            $result = $this->ai->complete($this->systemPrompt(), $query, $this->jsonSchema());
        } catch (AiException $e) {
            Log::warning('ai_nutrition.failed', ['query' => $query, 'error' => $e->getMessage()]);

            return null;
        }

        $data = json_decode($result->content, true);
        if (! is_array($data) || ($data['known'] ?? false) !== true) {
            return null;
        }

        $facts = new NutritionFacts(
            name: (string) ($data['name'] ?? $query),
            brand: $data['brand'] ?? null,
            kcal: (float) ($data['kcal_100g'] ?? 0),
            proteinG: (float) ($data['protein_g_100g'] ?? 0),
            carbG: (float) ($data['carb_g_100g'] ?? 0),
            fatG: (float) ($data['fat_g_100g'] ?? 0),
            fiberG: isset($data['fiber_g_100g']) ? (float) $data['fiber_g_100g'] : null,
            sugarG: isset($data['sugar_g_100g']) ? (float) $data['sugar_g_100g'] : null,
            locale: $data['locale'] ?? 'es-MX',
            portions: $this->mapPortions($data['portions'] ?? []),
            raw: $data + [
                'tokens' => [$result->promptTokens, $result->completionTokens],
            ],
        );

        return $facts->looksValid() ? $facts : null;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
        Eres una base de datos nutricional con enfoque en cocina mexicana e internacional.
        El usuario te da el nombre de un alimento o platillo. Devuelve sus valores
        nutricionales POR 100 GRAMOS de porción comestible, como JSON.

        Reglas:
        - Si NO conoces el alimento con confianza razonable, responde {"known": false}.
        - Usa valores típicos de una preparación estándar (sin extremos).
        - "portions" debe incluir 1-3 porciones caseras realistas (ej. "1 taco" = 90 g).
        - No inventes marcas; deja "brand" en null salvo que el nombre incluya una.
        - Todos los números son por 100 g, no por porción.
        PROMPT;
    }

    /** @return array<string, mixed> */
    private function jsonSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['known'],
            'properties' => [
                'known' => ['type' => 'boolean'],
                'name' => ['type' => ['string', 'null']],
                'brand' => ['type' => ['string', 'null']],
                'locale' => ['type' => ['string', 'null']],
                'kcal_100g' => ['type' => ['number', 'null']],
                'protein_g_100g' => ['type' => ['number', 'null']],
                'carb_g_100g' => ['type' => ['number', 'null']],
                'fat_g_100g' => ['type' => ['number', 'null']],
                'fiber_g_100g' => ['type' => ['number', 'null']],
                'sugar_g_100g' => ['type' => ['number', 'null']],
                'portions' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['label', 'grams'],
                        'properties' => [
                            'label' => ['type' => 'string'],
                            'grams' => ['type' => 'number'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<int, mixed>  $portions
     * @return array<int, array{label: string, grams: float}>
     */
    private function mapPortions(array $portions): array
    {
        return collect($portions)
            ->filter(fn ($p) => is_array($p) && isset($p['label'], $p['grams']))
            ->map(fn ($p) => ['label' => (string) $p['label'], 'grams' => (float) $p['grams']])
            ->filter(fn ($p) => $p['grams'] > 0 && $p['grams'] <= 2000)
            ->values()
            ->all();
    }
}
