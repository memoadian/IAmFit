<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum Mechanic: string
{
    use HasValues;

    case Compound = 'compound';
    case Isolation = 'isolation';
}
