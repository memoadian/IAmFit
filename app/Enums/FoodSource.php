<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum FoodSource: string
{
    use HasValues;

    case Off = 'off';
    case Usda = 'usda';
    case Ai = 'ai';
    case Manual = 'manual';
}
