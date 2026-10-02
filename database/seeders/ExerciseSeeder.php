<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Muscle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $muscles = Muscle::pluck('id', 'slug');

        // [nombre, músculo primario, equipo, mecánica, [músculos secundarios]]
        $exercises = [
            ['Press de banca con barra', 'pectoral-mayor', 'barbell', 'compound', ['deltoide-anterior', 'triceps']],
            ['Press inclinado con mancuernas', 'pectoral-superior', 'dumbbell', 'compound', ['deltoide-anterior', 'triceps']],
            ['Aperturas con mancuernas', 'pectoral-mayor', 'dumbbell', 'isolation', []],
            ['Fondos en paralelas', 'pectoral-mayor', 'bodyweight', 'compound', ['triceps', 'deltoide-anterior']],
            ['Dominadas', 'dorsal-ancho', 'bodyweight', 'compound', ['biceps', 'romboides']],
            ['Remo con barra', 'dorsal-ancho', 'barbell', 'compound', ['trapecio', 'biceps', 'romboides']],
            ['Jalón al pecho', 'dorsal-ancho', 'cable', 'compound', ['biceps']],
            ['Remo sentado en polea', 'romboides', 'cable', 'compound', ['dorsal-ancho', 'biceps']],
            ['Peso muerto convencional', 'isquiotibiales', 'barbell', 'compound', ['gluteo', 'lumbar', 'trapecio']],
            ['Hip thrust', 'gluteo', 'barbell', 'compound', ['isquiotibiales']],
            ['Sentadilla trasera con barra', 'cuadriceps', 'barbell', 'compound', ['gluteo', 'isquiotibiales', 'lumbar']],
            ['Prensa de piernas', 'cuadriceps', 'machine', 'compound', ['gluteo']],
            ['Extensión de cuádriceps', 'cuadriceps', 'machine', 'isolation', []],
            ['Curl femoral tumbado', 'isquiotibiales', 'machine', 'isolation', []],
            ['Zancadas con mancuernas', 'cuadriceps', 'dumbbell', 'compound', ['gluteo', 'isquiotibiales']],
            ['Elevación de talones de pie', 'gemelos', 'machine', 'isolation', []],
            ['Press militar con barra', 'deltoide-anterior', 'barbell', 'compound', ['deltoide-lateral', 'triceps']],
            ['Elevaciones laterales', 'deltoide-lateral', 'dumbbell', 'isolation', []],
            ['Pájaros (posterior)', 'deltoide-posterior', 'dumbbell', 'isolation', ['romboides']],
            ['Curl de bíceps con barra', 'biceps', 'barbell', 'isolation', ['antebrazo']],
            ['Curl martillo', 'biceps', 'dumbbell', 'isolation', ['antebrazo']],
            ['Extensión de tríceps en polea', 'triceps', 'cable', 'isolation', []],
            ['Press francés', 'triceps', 'barbell', 'isolation', []],
            ['Plancha abdominal', 'recto-abdominal', 'bodyweight', 'isolation', ['oblicuos']],
            ['Crunch en polea', 'recto-abdominal', 'cable', 'isolation', []],
            ['Elevación de piernas colgado', 'recto-abdominal', 'bodyweight', 'isolation', ['oblicuos']],
        ];

        foreach ($exercises as [$name, $primary, $equipment, $mechanic, $secondary]) {
            $exercise = Exercise::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'primary_muscle_id' => $muscles[$primary],
                    'equipment' => $equipment,
                    'mechanic' => $mechanic,
                    'is_public' => true,
                ],
            );

            $exercise->secondaryMuscles()->sync(
                collect($secondary)->map(fn ($slug) => $muscles[$slug])->all()
            );
        }
    }
}
