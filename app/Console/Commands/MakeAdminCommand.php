<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Otorga o revoca acceso al panel Filament (/admin).
 *
 *   php artisan iamfit:make-admin memo@example.com
 *   php artisan iamfit:make-admin memo@example.com --revoke
 */
class MakeAdminCommand extends Command
{
    protected $signature = 'iamfit:make-admin {email : Correo del usuario}
                            {--revoke : Quita el acceso en vez de otorgarlo}';

    protected $description = 'Otorga o revoca acceso al panel de administración';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No existe un usuario con el correo {$email}.");

            return self::FAILURE;
        }

        $user->is_admin = ! $this->option('revoke');
        $user->save();

        $this->info($user->is_admin
            ? "{$email} ya tiene acceso al panel /admin."
            : "Se revocó el acceso de {$email} al panel /admin.");

        return self::SUCCESS;
    }
}
