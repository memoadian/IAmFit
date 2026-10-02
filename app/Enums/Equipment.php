<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum Equipment: string
{
    use HasValues;

    case Barbell = 'barbell';
    case Dumbbell = 'dumbbell';
    case Machine = 'machine';
    case Cable = 'cable';
    case Bodyweight = 'bodyweight';
    case Kettlebell = 'kettlebell';
    case Band = 'band';
    case Other = 'other';
}
