<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    /**
     * Semilla mínima de alimentos base mexicanos (verificados a mano) para que
     * las búsquedas más comunes no dependan de una llamada externa. El grueso
     * del catálogo lo llenan Open Food Facts / USDA / IA sobre la marcha.
     *
     * Valores por 100 g.
     */
    public function run(): void
    {
        $foods = [
            // [nombre, kcal, prot, carb, grasa, fibra, porciones]
            ['Tortilla de maíz', 218, 5.7, 44.6, 2.9, 6.3, [['1 tortilla', 26]]],
            ['Tortilla de harina', 312, 8.2, 51.0, 7.9, 3.0, [['1 tortilla', 35]]],
            ['Frijoles negros cocidos', 132, 8.9, 23.7, 0.5, 8.7, [['1 taza', 172]]],
            ['Arroz blanco cocido', 130, 2.7, 28.2, 0.3, 0.4, [['1 taza', 158]]],
            ['Pechuga de pollo cocida', 165, 31.0, 0.0, 3.6, 0.0, [['1 pieza', 120]]],
            ['Huevo entero', 143, 12.6, 0.7, 9.5, 0.0, [['1 pieza', 50]]],
            ['Aguacate Hass', 160, 2.0, 8.5, 14.7, 6.7, [['1/2 pieza', 68]]],
            ['Queso Oaxaca', 356, 26.0, 3.0, 27.0, 0.0, [['1 porción', 30]]],
            ['Leche entera', 61, 3.2, 4.8, 3.3, 0.0, [['1 taza', 244]]],
            ['Plátano', 89, 1.1, 22.8, 0.3, 2.6, [['1 pieza', 118]]],
            ['Avena en hojuelas', 389, 16.9, 66.3, 6.9, 10.6, [['1/2 taza', 40]]],
            ['Tostada horneada', 434, 8.0, 68.0, 14.0, 6.0, [['1 tostada', 12]]],
        ];

        foreach ($foods as [$name, $kcal, $protein, $carb, $fat, $fiber, $portions]) {
            $food = Food::updateOrCreate(
                ['source' => 'manual', 'name' => $name],
                [
                    'kcal' => $kcal,
                    'protein_g' => $protein,
                    'carb_g' => $carb,
                    'fat_g' => $fat,
                    'fiber_g' => $fiber,
                    'locale' => 'es-MX',
                    'verified_at' => now(),
                ],
            );

            $food->portions()->delete();
            $food->portions()->create(['label' => '100 g', 'grams' => 100, 'is_default' => false]);
            foreach ($portions as $i => [$label, $grams]) {
                $food->portions()->create([
                    'label' => $label,
                    'grams' => $grams,
                    'is_default' => $i === 0,
                ]);
            }
        }
    }
}
