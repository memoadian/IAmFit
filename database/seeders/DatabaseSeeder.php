<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Food;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MuscleSeeder::class,
            ExerciseSeeder::class,
            FoodSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->seedDemoData();
        }
    }

    /**
     * Datos demo de desarrollo (P2-16): usuario con perfil, 7 días de peso, una
     * rutina de 3 días y diario de los últimos 2 días.
     */
    private function seedDemoData(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@iamfit.local'],
            ['name' => 'Demo', 'password' => 'password'],
        );

        $user->profile()->updateOrCreate([], [
            'sex' => 'male',
            'birthdate' => '1994-03-01',
            'height_cm' => 178,
            'activity_level' => 'moderate',
            'goal' => 'lose',
            'goal_rate_kg_per_week' => -0.5,
            'locale' => 'es-MX',
            'timezone' => 'America/Mexico_City',
        ]);

        foreach (range(6, 0) as $daysAgo) {
            $user->bodyWeightEntries()->updateOrCreate(
                ['measured_on' => today()->subDays($daysAgo)->toDateString()],
                ['weight_kg' => round(84 - (6 - $daysAgo) * 0.2, 1), 'source' => 'manual'],
            );
        }

        $this->seedRoutine($user);
        $this->seedDiary($user);
    }

    private function seedRoutine(User $user): void
    {
        if ($user->routines()->exists()) {
            return;
        }

        $slugs = [
            'full-body' => [
                'Sentadilla trasera con barra',
                'Press de banca con barra',
                'Remo con barra',
            ],
            'push' => [
                'Press militar con barra',
                'Press inclinado con mancuernas',
                'Extensión de tríceps en polea',
            ],
            'pull' => [
                'Dominadas',
                'Jalón al pecho',
                'Curl de bíceps con barra',
            ],
        ];

        $exercises = Exercise::whereIn('name', collect($slugs)->flatten())->get()->keyBy('name');

        $routine = $user->routines()->create([
            'name' => 'Full body 3x',
            'notes' => 'Rutina demo sembrada para desarrollo.',
            'days_per_week' => 3,
            'is_active' => true,
        ]);

        foreach (array_values($slugs) as $index => $names) {
            $day = $routine->days()->create([
                'label' => 'Día '.chr(65 + $index),
                'position' => $index + 1,
            ]);

            foreach ($names as $position => $name) {
                $exercise = $exercises->get($name);

                if (! $exercise) {
                    continue;
                }

                $day->exercises()->create([
                    'exercise_id' => $exercise->id,
                    'position' => $position + 1,
                    'target_sets' => 4,
                    'target_reps_min' => 8,
                    'target_reps_max' => 12,
                    'target_rpe' => 8,
                ]);
            }
        }
    }

    private function seedDiary(User $user): void
    {
        if ($user->foodLogEntries()->exists()) {
            return;
        }

        $meals = [
            ['Avena en hojuelas', 'breakfast', 60],
            ['Leche entera', 'breakfast', 244],
            ['Pechuga de pollo cocida', 'lunch', 150],
            ['Arroz blanco cocido', 'lunch', 158],
            ['Tortilla de maíz', 'dinner', 78],
            ['Frijoles negros cocidos', 'dinner', 172],
        ];

        foreach ([today(), today()->subDay()] as $date) {
            foreach ($meals as [$name, $meal, $grams]) {
                $food = Food::where('name', $name)->first();

                if (! $food) {
                    continue;
                }

                $user->foodLogEntries()->create([
                    'food_id' => $food->id,
                    'meal' => $meal,
                    'consumed_on' => $date->toDateString(),
                    'quantity' => 1,
                    'grams' => $grams,
                    ...$food->nutrientsForGrams((float) $grams),
                ]);
            }
        }
    }
}
