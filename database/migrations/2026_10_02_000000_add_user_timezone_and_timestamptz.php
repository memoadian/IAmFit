<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * P1-8 del plan: guardar instantes como `timestamptz` y permitir la zona horaria
 * del usuario, para no calcular el "día local" con la zona del servidor.
 *
 * Se hace como migración explícita (no editando las migraciones históricas) para
 * que también aplique a bases de datos ya existentes. Las columnas actuales
 * guardan instantes en UTC, por eso el `USING ... AT TIME ZONE 'UTC'`.
 */
return new class extends Migration
{
    /** @var array<string, array<int, string>> */
    private const TIMESTAMPTZ_COLUMNS = [
        'users' => ['created_at', 'updated_at', 'email_verified_at'],
        'password_reset_tokens' => ['created_at'],
        'personal_access_tokens' => ['created_at', 'updated_at', 'last_used_at', 'expires_at'],
        'failed_jobs' => ['failed_at'],
        'profiles' => ['created_at', 'updated_at'],
        'body_weight_entries' => ['created_at', 'updated_at'],
        'foods' => ['created_at', 'updated_at', 'verified_at'],
        'food_portions' => ['created_at', 'updated_at'],
        'ai_food_lookups' => ['created_at', 'updated_at'],
        'food_log_entries' => ['created_at', 'updated_at'],
        'muscles' => ['created_at', 'updated_at'],
        'exercises' => ['created_at', 'updated_at'],
        'routines' => ['created_at', 'updated_at'],
        'routine_days' => ['created_at', 'updated_at'],
        'routine_exercises' => ['created_at', 'updated_at'],
        'workout_sessions' => ['created_at', 'updated_at', 'performed_at'],
        'set_logs' => ['created_at', 'updated_at'],
        'ai_training_advices' => ['created_at', 'updated_at'],
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('profiles', 'timezone')) {
            Schema::table('profiles', function (Blueprint $table) {
                // Zona horaria IANA del usuario (ej. America/Mexico_City).
                $table->string('timezone', 64)->nullable()->after('locale');
            });
        }

        $this->alterColumns('timestamptz');
    }

    public function down(): void
    {
        $this->alterColumns('timestamp');

        if (Schema::hasColumn('profiles', 'timezone')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropColumn('timezone');
            });
        }
    }

    private function alterColumns(string $type): void
    {
        foreach (self::TIMESTAMPTZ_COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::statement(sprintf(
                    'ALTER TABLE "%s" ALTER COLUMN "%s" TYPE %s USING "%s" AT TIME ZONE \'UTC\'',
                    $table,
                    $column,
                    $type,
                    $column,
                ));
            }
        }
    }
};
