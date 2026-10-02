<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum LookupStatus: string
{
    use HasValues;

    case Pending = 'pending';
    case Processing = 'processing';
    case Done = 'done';
    case Failed = 'failed';
}
