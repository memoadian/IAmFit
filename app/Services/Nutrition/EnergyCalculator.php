<?php

namespace App\Services\Nutrition;

use App\Models\Profile;

/**
 * Cálculo de gasto energético y objetivos de macros. Todo determinista, sin IA:
 * la IA nunca decide cuántas kcal necesita el usuario.
 *
 *  - BMR (tasa metabólica basal): Mifflin-St Jeor, el estándar clínico actual.
 *  - TDEE (gasto total diario): BMR × factor de actividad.
 *  - Objetivo calórico: TDEE ajustado por el ritmo de cambio de peso deseado
 *    (~7700 kcal por kg de tejido graso).
 */
class EnergyCalculator
{
    private const KCAL_PER_KG = 7700;

    /** Ritmo por defecto (kg/semana) cuando el perfil no especifica uno. */
    private const DEFAULT_RATE = [
        'lose' => -0.5,
        'maintain' => 0.0,
        'gain' => 0.25,
    ];

    public function bmr(Profile $profile, float $weightKg): float
    {
        $base = 10 * $weightKg
            + 6.25 * $profile->height_cm
            - 5 * $profile->ageYears();

        $sexOffset = $profile->sex === 'male' ? 5 : -161;

        return round($base + $sexOffset, 0);
    }

    public function tdee(Profile $profile, float $weightKg): float
    {
        return round($this->bmr($profile, $weightKg) * $profile->activityFactor(), 0);
    }

    /**
     * @return array{
     *   weight_kg: float, age: int, bmr: float, tdee: float,
     *   goal: string, rate_kg_per_week: float, target_kcal: float,
     *   macros: array{protein_g: int, fat_g: int, carb_g: int}
     * }
     */
    public function summary(Profile $profile, float $weightKg): array
    {
        $tdee = $this->tdee($profile, $weightKg);

        $rate = $profile->goal_rate_kg_per_week
            ?? self::DEFAULT_RATE[$profile->goal]
            ?? 0.0;

        $dailyAdjustment = $rate * self::KCAL_PER_KG / 7;
        // No bajar de un piso razonable respecto al BMR aunque el ritmo sea agresivo.
        $targetKcal = max(
            round($tdee + $dailyAdjustment, 0),
            round($this->bmr($profile, $weightKg) * 1.1, 0),
        );

        return [
            'weight_kg' => $weightKg,
            'age' => $profile->ageYears(),
            'bmr' => $this->bmr($profile, $weightKg),
            'tdee' => $tdee,
            'goal' => $profile->goal,
            'rate_kg_per_week' => round($rate, 2),
            'target_kcal' => $targetKcal,
            'macros' => $this->macros($targetKcal, $weightKg),
        ];
    }

    /**
     * Reparto simple: proteína 1.8 g/kg, grasa 25% de las kcal, resto carbos.
     *
     * @return array{protein_g: int, fat_g: int, carb_g: int}
     */
    private function macros(float $targetKcal, float $weightKg): array
    {
        $proteinG = 1.8 * $weightKg;
        $fatG = ($targetKcal * 0.25) / 9;
        $carbG = ($targetKcal - ($proteinG * 4) - ($fatG * 9)) / 4;

        return [
            'protein_g' => (int) round($proteinG),
            'fat_g' => (int) round($fatG),
            'carb_g' => (int) round(max($carbG, 0)),
        ];
    }
}
