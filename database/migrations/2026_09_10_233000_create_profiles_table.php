<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('sex', ['male', 'female']);
            $table->date('birthdate');
            $table->decimal('height_cm', 5, 1);
            // Factor de actividad para el TDEE (multiplicador sobre el BMR).
            $table->enum('activity_level', [
                'sedentary', 'light', 'moderate', 'active', 'very_active',
            ])->default('moderate');
            $table->enum('goal', ['lose', 'maintain', 'gain'])->default('maintain');
            // Ritmo objetivo en kg/semana (negativo = déficit). Null = deja que
            // el objetivo defina un ritmo por defecto.
            $table->decimal('goal_rate_kg_per_week', 3, 2)->nullable();
            $table->string('locale', 10)->default('es-MX');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
