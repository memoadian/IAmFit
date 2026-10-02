<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('set_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('set_number');
            $table->decimal('weight_kg', 6, 2)->default(0);
            $table->unsignedSmallInteger('reps');
            $table->decimal('rpe', 3, 1)->nullable();
            $table->boolean('is_warmup')->default(false);
            $table->timestamps();

            $table->index(['exercise_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('set_logs');
    }
};
