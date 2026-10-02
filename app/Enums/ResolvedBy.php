<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ResolvedBy: string
{
    use HasValues;

    case Off = 'off';
    case Usda = 'usda';
    case Ai = 'ai';
}
