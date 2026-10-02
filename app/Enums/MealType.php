<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum MealType: string
{
    use HasValues;

    case Breakfast = 'breakfast';
    case Lunch = 'lunch';
    case Dinner = 'dinner';
    case Snack = 'snack';
}
