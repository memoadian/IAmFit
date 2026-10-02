<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_day_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('position')->default(1);

            $table->unsignedTinyInteger('target_sets')->default(3);
            $table->unsignedSmallInteger('target_reps_min')->default(8);
            $table->unsignedSmallInteger('target_reps_max')->default(12);
            // RPE objetivo (esfuerzo percibido, 6.0–10.0) o null si no aplica.
            $table->decimal('target_rpe', 3, 1)->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->string('note')->nullable();

            $table->timestamps();

            $table->index('routine_day_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_exercises');
    }
};
