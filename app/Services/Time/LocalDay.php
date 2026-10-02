<?php

namespace App\Services\Time;

use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Resuelve la zona horaria del usuario y el "día local" sin depender de la zona
 * del servidor. Orden de preferencia:
 *
 *   1. `timezone` del request (validado).
 *   2. `timezone` guardada en el perfil.
 *   3. `config('app.user_timezone')`.
 *   4. UTC.
 */
final class LocalDay
{
    public static function timezone(?User $user = null, ?string $requested = null): string
    {
        foreach ([$requested, $user?->profile?->timezone, config('app.user_timezone'), 'UTC'] as $candidate) {
            if (is_string($candidate) && $candidate !== '' && in_array($candidate, timezone_identifiers_list(), true)) {
                return $candidate;
            }
        }

        return 'UTC';
    }

    public static function today(?User $user = null, ?string $requested = null): CarbonImmutable
    {
        return CarbonImmutable::now(self::timezone($user, $requested))->startOfDay();
    }
}
