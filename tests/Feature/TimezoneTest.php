<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimezoneTest extends TestCase
{
    use RefreshDatabase;

    private function food(): Food
    {
        return Food::create([
            'name' => 'Taco',
            'source' => 'manual',
            'kcal' => 200, 'protein_g' => 10, 'carb_g' => 20, 'fat_g' => 8,
            'verified_at' => now(),
        ]);
    }

    public function test_profile_stores_the_timezone(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson('/api/profile', [
            'sex' => 'male',
            'birthdate' => '1994-03-01',
            'height_cm' => 180,
            'activity_level' => 'moderate',
            'goal' => 'maintain',
            'timezone' => 'America/Mexico_City',
        ])->assertOk();

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'timezone' => 'America/Mexico_City',
        ]);
    }

    public function test_diary_uses_the_local_day_not_the_server_day(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-02 02:30:00', 'UTC'));

        $user = User::factory()->create();
        $food = $this->food();

        $this->actingAs($user)->postJson('/api/diary', [
            'food_id' => $food->id,
            'meal' => 'dinner',
            'grams' => 100,
            'timezone' => 'America/Mexico_City',
        ])->assertCreated();

        // En UTC ya es 2 de octubre; en Ciudad de México sigue siendo 1 de octubre.
        $this->assertDatabaseHas('food_log_entries', [
            'user_id' => $user->id,
            'consumed_on' => '2026-10-01',
        ]);

        $this->actingAs($user)
            ->getJson('/api/diary?timezone=America/Mexico_City')
            ->assertOk()
            ->assertJsonPath('data.date', '2026-10-01');
    }
}
