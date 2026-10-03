<?php

namespace Tests\Feature;

use App\Jobs\ResolveFoodLookup;
use App\Models\Food;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FoodDiaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_a_known_food_from_the_db(): void
    {
        $user = User::factory()->create();
        $food = Food::create([
            'name' => 'Pechuga de pollo asada',
            'source' => 'manual',
            'kcal' => 165, 'protein_g' => 31, 'carb_g' => 0, 'fat_g' => 3.6,
            'verified_at' => now(),
        ]);
        $food->portions()->create(['label' => '100 g', 'grams' => 100, 'is_default' => true]);

        $this->actingAs($user)->getJson('/api/foods/search?q=pechuga+de+pollo')
            ->assertOk()
            ->assertJsonPath('data.status', 'found')
            ->assertJsonPath('data.food.id', $food->id)
            ->assertJsonPath('data.food.per_100g.protein_g', 31);
    }

    public function test_unknown_food_queues_an_enrichment_lookup(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/foods/search?q=tlacoyo+de+haba')
            ->assertStatus(202)
            ->assertJsonPath('data.status', 'pending');

        $lookupId = $response->json('data.lookup_id');
        $this->assertDatabaseHas('ai_food_lookups', ['id' => $lookupId, 'status' => 'pending']);
        Queue::assertPushed(ResolveFoodLookup::class);
    }

    public function test_logging_a_food_snapshots_nutrients_and_totals_the_diary(): void
    {
        $user = User::factory()->create();
        $food = Food::create([
            'name' => 'Arroz blanco cocido',
            'source' => 'manual',
            'kcal' => 130, 'protein_g' => 2.7, 'carb_g' => 28.2, 'fat_g' => 0.3,
            'verified_at' => now(),
        ]);
        $portion = $food->portions()->create(['label' => '1 taza', 'grams' => 158, 'is_default' => true]);

        $this->actingAs($user)->postJson('/api/diary', [
            'food_id' => $food->id,
            'food_portion_id' => $portion->id,
            'quantity' => 2,
            'meal' => 'lunch',
        ])->assertCreated();

        // 316 g -> kcal = 130 * 3.16 = 410.8
        $this->assertDatabaseHas('food_log_entries', [
            'user_id' => $user->id,
            'grams' => 316,
        ]);

        $this->actingAs($user)->getJson('/api/diary')
            ->assertOk()
            ->assertJsonPath('data.totals.kcal', 410.8);
    }

    public function test_diary_entries_is_an_object_even_when_there_are_no_entries(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/diary')->assertOk();

        $payload = json_decode($response->getContent());

        $this->assertIsObject($payload->data->entries);
        $this->assertSame(0, $payload->data->totals->kcal);
    }
}
