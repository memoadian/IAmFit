<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ActivityLevel: string
{
    use HasValues;

    case Sedentary = 'sedentary';
    case Light = 'light';
    case Moderate = 'moderate';
    case Active = 'active';
    case VeryActive = 'very_active';

    /** Multiplicador sobre el BMR para estimar el TDEE. */
    public function factor(): float
    {
        return match ($this) {
            self::Sedentary => 1.2,
            self::Light => 1.375,
            self::Moderate => 1.55,
            self::Active => 1.725,
            self::VeryActive => 1.9,
        };
    }
}
