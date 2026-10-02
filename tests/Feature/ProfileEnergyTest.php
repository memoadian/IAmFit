<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileEnergyTest extends TestCase
{
    use RefreshDatabase;

    public function test_energy_needs_a_profile_and_a_weight(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/energy')->assertStatus(422);
    }

    public function test_upserting_a_profile_with_weight_enables_the_energy_summary(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson('/api/profile', [
            'sex' => 'male',
            'birthdate' => '1994-03-01',
            'height_cm' => 180,
            'activity_level' => 'moderate',
            'goal' => 'lose',
            'goal_rate_kg_per_week' => -0.5,
            'weight_kg' => 82,
        ])->assertOk()->assertJsonPath('data.latest_weight_kg', 82);

        $this->assertDatabaseHas('profiles', ['user_id' => $user->id, 'goal' => 'lose']);
        $this->assertDatabaseHas('body_weight_entries', ['user_id' => $user->id, 'weight_kg' => 82]);

        $summary = $this->actingAs($user)->getJson('/api/energy')->assertOk()->json();

        $this->assertGreaterThan(1400, $summary['data']['bmr']);
        $this->assertGreaterThan($summary['data']['bmr'], $summary['data']['tdee']);
        $this->assertLessThan($summary['data']['tdee'], $summary['data']['target_kcal']);
        $this->assertArrayHasKey('protein_g', $summary['data']['macros']);
    }
}
