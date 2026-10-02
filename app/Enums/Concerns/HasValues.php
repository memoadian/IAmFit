<?php

namespace App\Enums\Concerns;

/**
 * Azúcar para backed enums: expone los casos como arreglo de valores, útil para
 * `Rule::in(...)` y para códigos que antes usaban arrays de strings.
 */
trait HasValues
{
    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, static::cases());
    }
}
