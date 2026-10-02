<?php

namespace Tests\Feature;

use App\Models\User;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * P1-9: cubre el pipeline de enriquecimiento (Open Food Facts → USDA → IA) con
 * `Http::fake()`, sin pegarle a ningún proveedor real.
 */
class FoodEnrichmentTest extends TestCase
{
    use RefreshDatabase;

    private function groqResponse(array $payload): PromiseInterface
    {
        return Http::response([
            'choices' => [[
                'message' => ['content' => json_encode($payload, JSON_UNESCAPED_UNICODE)],
                'finish_reason' => 'stop',
            ]],
            'usage' => ['prompt_tokens' => 120, 'completion_tokens' => 60],
        ], 200);
    }

    public function test_unknown_food_is_enriched_by_the_ai_and_marked_unverified(): void
    {
        config(['services.groq.api_key' => 'test-key']);

        Http::fake([
            'search.openfoodfacts.org/*' => Http::response(['hits' => []], 200),
            'api.groq.com/*' => $this->groqResponse([
                'known' => true,
                'name' => 'Tlacoyo de haba',
                'brand' => null,
                'locale' => 'es-MX',
                'kcal_100g' => 180,
                'protein_g_100g' => 7.5,
                'carb_g_100g' => 24,
                'fat_g_100g' => 5.5,
                'portions' => [['label' => '1 pieza', 'grams' => 90]],
            ]),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/foods/search?q='.urlencode('tlacoyo de haba'))
            ->assertStatus(202)
            ->assertJsonPath('data.status', 'pending');

        $lookupId = $response->json('data.lookup_id');

        $this->actingAs($user)->getJson("/api/foods/lookups/{$lookupId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'done')
            ->assertJsonPath('data.resolved_by', 'ai')
            ->assertJsonPath('data.food.name', 'Tlacoyo de haba')
            ->assertJsonPath('data.food.is_verified', false);

        $this->assertDatabaseHas('foods', ['source' => 'ai', 'verified_at' => null]);
        $this->assertDatabaseHas('ai_food_lookups', [
            'id' => $lookupId,
            'status' => 'done',
            'prompt_tokens' => 120,
            'completion_tokens' => 60,
        ]);
    }

    public function test_open_food_facts_is_preferred_over_the_ai(): void
    {
        config(['services.groq.api_key' => 'test-key']);

        Http::fake([
            'search.openfoodfacts.org/*' => Http::response([
                'hits' => [[
                    'code' => '7501234567890',
                    'product_name' => 'Tlacoyo de haba',
                    'brands' => 'Marca Local',
                    'nutriments' => [
                        'energy-kcal_100g' => 175,
                        'proteins_100g' => 7,
                        'carbohydrates_100g' => 23,
                        'fat_100g' => 5,
                    ],
                    'lang' => 'es',
                ]],
            ], 200),
        ]);

        $user = User::factory()->create();

        $lookupId = $this->actingAs($user)
            ->getJson('/api/foods/search?q='.urlencode('tlacoyo de haba'))
            ->assertStatus(202)
            ->json('data.lookup_id');

        $this->actingAs($user)->getJson("/api/foods/lookups/{$lookupId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'done')
            ->assertJsonPath('data.resolved_by', 'off')
            ->assertJsonPath('data.food.brand', 'Marca Local');

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'api.groq.com'));
    }

    public function test_lookup_fails_when_no_source_knows_the_food(): void
    {
        config(['services.groq.api_key' => 'test-key']);

        Http::fake([
            'search.openfoodfacts.org/*' => Http::response(['hits' => []], 200),
            'api.groq.com/*' => $this->groqResponse(['known' => false]),
        ]);

        $user = User::factory()->create();

        $lookupId = $this->actingAs($user)
            ->getJson('/api/foods/search?q='.urlencode('platillo inexistente xyz'))
            ->assertStatus(202)
            ->json('data.lookup_id');

        $this->actingAs($user)->getJson("/api/foods/lookups/{$lookupId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.food', null);

        $this->assertDatabaseHas('ai_food_lookups', [
            'id' => $lookupId,
            'status' => 'failed',
            'error' => 'Ninguna fuente devolvió datos.',
        ]);
    }
}
