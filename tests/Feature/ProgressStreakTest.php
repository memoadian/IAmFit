<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressStreakTest extends TestCase
{
    use RefreshDatabase;

    private function food(): Food
    {
        return Food::create([
            'name' => 'Avena',
            'source' => 'manual',
            'kcal' => 380, 'protein_g' => 13, 'carb_g' => 67, 'fat_g' => 7,
            'verified_at' => now(),
        ]);
    }

    public function test_streak_counts_days_with_food_or_workouts(): void
    {
        $user = User::factory()->create();
        $food = $this->food();

        // Hoy: comida + entreno. Ayer: solo entreno. Antier: nada.
        $user->foodLogEntries()->create([
            'food_id' => $food->id, 'meal' => 'breakfast',
            'consumed_on' => today()->toDateString(), 'quantity' => 1, 'grams' => 100,
            'kcal' => 380, 'protein_g' => 13, 'carb_g' => 67, 'fat_g' => 7,
        ]);
        $user->workoutSessions()->create(['performed_at' => now()]);
        $user->workoutSessions()->create(['performed_at' => now()->subDay()]);

        $response = $this->actingAs($user)->getJson('/api/progress/streak')
            ->assertOk()
            ->assertJsonPath('data.current_streak', 2)
            ->assertJsonCount(7, 'data.days');

        $days = collect($response->json('data.days'))->keyBy('date');

        $this->assertTrue($days[today()->toDateString()]['completed']);
        $this->assertTrue($days[today()->subDay()->toDateString()]['completed']);
        $this->assertFalse($days[today()->subDays(2)->toDateString()]['completed']);
    }

    public function test_streak_respects_the_requested_timezone(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-02 02:30:00', 'UTC'));

        $user = User::factory()->create();
        $food = $this->food();

        // 02:30 UTC del día 2 = 20:30 del día 1 en Ciudad de México.
        $user->foodLogEntries()->create([
            'food_id' => $food->id, 'meal' => 'dinner',
            'consumed_on' => '2026-10-01', 'quantity' => 1, 'grams' => 100,
            'kcal' => 380, 'protein_g' => 13, 'carb_g' => 67, 'fat_g' => 7,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/progress/streak?timezone=America/Mexico_City')
            ->assertOk();

        $days = collect($response->json('data.days'))->keyBy('date');
        $this->assertTrue($days['2026-10-01']['completed']);
    }
}
