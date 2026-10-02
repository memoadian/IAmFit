<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum MuscleGroup: string
{
    use HasValues;

    case Chest = 'chest';
    case Back = 'back';
    case Shoulders = 'shoulders';
    case Arms = 'arms';
    case Legs = 'legs';
    case Core = 'core';
}
