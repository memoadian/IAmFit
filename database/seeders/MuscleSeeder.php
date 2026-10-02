<?php

namespace Database\Seeders;

use App\Models\Muscle;
use Illuminate\Database\Seeder;

class MuscleSeeder extends Seeder
{
    public function run(): void
    {
        $muscles = [
            ['chest', 'pectoral-mayor', 'Pectoral mayor'],
            ['chest', 'pectoral-superior', 'Pectoral superior'],
            ['back', 'dorsal-ancho', 'Dorsal ancho'],
            ['back', 'trapecio', 'Trapecio'],
            ['back', 'romboides', 'Romboides'],
            ['back', 'lumbar', 'Erectores espinales'],
            ['shoulders', 'deltoide-anterior', 'Deltoide anterior'],
            ['shoulders', 'deltoide-lateral', 'Deltoide lateral'],
            ['shoulders', 'deltoide-posterior', 'Deltoide posterior'],
            ['arms', 'biceps', 'Bíceps'],
            ['arms', 'triceps', 'Tríceps'],
            ['arms', 'antebrazo', 'Antebrazo'],
            ['legs', 'cuadriceps', 'Cuádriceps'],
            ['legs', 'isquiotibiales', 'Isquiotibiales'],
            ['legs', 'gluteo', 'Glúteo'],
            ['legs', 'gemelos', 'Gemelos'],
            ['core', 'recto-abdominal', 'Recto abdominal'],
            ['core', 'oblicuos', 'Oblicuos'],
        ];

        foreach ($muscles as [$group, $slug, $name]) {
            Muscle::updateOrCreate(['slug' => $slug], compact('name', 'group'));
        }
    }
}
