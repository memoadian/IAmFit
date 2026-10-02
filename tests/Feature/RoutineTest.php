<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Muscle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineTest extends TestCase
{
    use RefreshDatabase;

    private function exercise(string $name): Exercise
    {
        $muscle = Muscle::firstOrCreate(
            ['slug' => 'pectoral-mayor'],
            ['name' => 'Pectoral mayor', 'group' => 'chest'],
        );

        return Exercise::create([
            'slug' => \Illuminate\Support\Str::slug($name),
            'name' => $name,
            'primary_muscle_id' => $muscle->id,
            'equipment' => 'barbell',
            'mechanic' => 'compound',
            'is_public' => true,
        ]);
    }

    public function test_a_user_can_create_a_routine_with_nested_days(): void
    {
        $user = User::factory()->create();
        $bench = $this->exercise('Press de banca');

        $response = $this->actingAs($user)->postJson('/api/routines', [
            'name' => 'Full body 3x',
            'days_per_week' => 3,
            'days' => [
                [
                    'label' => 'Día A',
                    'exercises' => [
                        ['exercise_id' => $bench->id, 'target_sets' => 4, 'target_reps_min' => 6, 'target_reps_max' => 10, 'target_rpe' => 8],
                    ],
                ],
            ],
        ])->assertCreated();

        $routineId = $response->json('data.id');
        $this->assertDatabaseHas('routines', ['id' => $routineId, 'user_id' => $user->id]);
        $this->assertDatabaseHas('routine_days', ['routine_id' => $routineId, 'label' => 'Día A']);
        $this->assertDatabaseHas('routine_exercises', ['exercise_id' => $bench->id, 'target_sets' => 4]);

        $response->assertJsonPath('data.days.0.exercises.0.exercise.name', 'Press de banca');
    }

    public function test_a_user_cannot_view_another_users_routine(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $bench = $this->exercise('Press de banca');

        $routineId = $this->actingAs($owner)->postJson('/api/routines', [
            'name' => 'Privada',
            'days' => [['label' => 'A', 'exercises' => [['exercise_id' => $bench->id]]]],
        ])->json('data.id');

        $this->actingAs($intruder)->getJson("/api/routines/{$routineId}")->assertForbidden();
    }
}
