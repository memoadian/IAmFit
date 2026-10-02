<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum Goal: string
{
    use HasValues;

    case Lose = 'lose';
    case Maintain = 'maintain';
    case Gain = 'gain';

    /** Ritmo por defecto en kg/semana cuando el perfil no especifica uno. */
    public function defaultRateKgPerWeek(): float
    {
        return match ($this) {
            self::Lose => -0.5,
            self::Maintain => 0.0,
            self::Gain => 0.25,
        };
    }
}
