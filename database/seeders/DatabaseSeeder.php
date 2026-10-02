<?php

namespace Database\Seeders;

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
            User::factory()->create([
                'name' => 'Memo',
                'email' => 'memoadian@gmail.com',
            ]);
        }
    }
}
