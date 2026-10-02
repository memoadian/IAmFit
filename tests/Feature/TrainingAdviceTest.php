<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Muscle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrainingAdviceTest extends TestCase
{
    use RefreshDatabase;

    private function routineId(User $user): int
    {
        $muscle = Muscle::firstOrCreate(
            ['slug' => 'pectoral-mayor'],
            ['name' => 'Pectoral mayor', 'group' => 'chest'],
        );

        $exercise = Exercise::create([
            'slug' => 'press-de-banca',
            'name' => 'Press de banca',
            'primary_muscle_id' => $muscle->id,
            'equipment' => 'barbell',
            'mechanic' => 'compound',
            'is_public' => true,
        ]);

        return $this->actingAs($user)->postJson('/api/routines', [
            'name' => 'Full body',
            'days' => [[
                'label' => 'Día A',
                'exercises' => [['exercise_id' => $exercise->id, 'target_sets' => 4]],
            ]],
        ])->assertCreated()->json('data.id');
    }

    public function test_routine_advice_is_generated_by_the_backend_ai(): void
    {
        config(['services.groq.api_key' => 'test-key']);

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => ['content' => 'Empieza con 60 kg y sube 2.5 kg por semana.'],
                    'finish_reason' => 'stop',
                ]],
                'usage' => ['prompt_tokens' => 200, 'completion_tokens' => 40],
            ], 200),
        ]);

        $user = User::factory()->create();
        $routineId = $this->routineId($user);

        $this->actingAs($user)->postJson("/api/routines/{$routineId}/advice")
            ->assertOk()
            ->assertJsonPath('data.advice', 'Empieza con 60 kg y sube 2.5 kg por semana.');

        $this->assertDatabaseHas('ai_training_advices', [
            'routine_id' => $routineId,
            'provider' => 'groq',
            'prompt_tokens' => 200,
        ]);
    }

    public function test_routine_advice_returns_a_safe_error_when_groq_rate_limits(): void
    {
        config(['services.groq.api_key' => 'test-key']);

        Http::fake([
            'api.groq.com/*' => Http::response(['error' => 'rate'], 429),
        ]);

        $user = User::factory()->create();
        $routineId = $this->routineId($user);

        $this->actingAs($user)->postJson("/api/routines/{$routineId}/advice")
            ->assertStatus(429)
            ->assertJsonStructure(['message']);
    }
}
