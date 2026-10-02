<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum WeightSource: string
{
    use HasValues;

    case Manual = 'manual';
    case Scale = 'scale';
    case Import = 'import';
}
