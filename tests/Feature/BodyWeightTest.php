<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BodyWeightTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_weight_entry_per_day_is_upserted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/weight', ['weight_kg' => 80])
            ->assertCreated()
            ->assertJsonPath('data.weight_kg', 80);

        $this->actingAs($user)->postJson('/api/weight', ['weight_kg' => 79.5])
            ->assertCreated();

        $this->assertDatabaseCount('body_weight_entries', 1);
        $this->assertSame(79.5, $user->latestWeightKg());
    }

    public function test_weight_index_can_be_limited_to_recent_days(): void
    {
        $user = User::factory()->create();
        $user->bodyWeightEntries()->create(['weight_kg' => 90, 'measured_on' => today()->subDays(30)]);
        $user->bodyWeightEntries()->create(['weight_kg' => 85, 'measured_on' => today()->subDay()]);

        $this->actingAs($user)->getJson('/api/weight?days=7')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.weight_kg', 85);
    }
}
