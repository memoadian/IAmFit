<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_secondary_muscle', function (Blueprint $table) {
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('muscle_id')->constrained()->cascadeOnDelete();
            $table->primary(['exercise_id', 'muscle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_secondary_muscle');
    }
};
