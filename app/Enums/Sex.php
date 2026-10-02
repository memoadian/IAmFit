<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum Sex: string
{
    use HasValues;

    case Male = 'male';
    case Female = 'female';
}
