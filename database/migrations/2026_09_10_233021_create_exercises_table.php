<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('primary_muscle_id')->constrained('muscles')->restrictOnDelete();
            $table->enum('equipment', [
                'barbell', 'dumbbell', 'machine', 'cable', 'bodyweight', 'kettlebell', 'band', 'other',
            ])->default('other');
            $table->enum('mechanic', ['compound', 'isolation'])->default('compound');
            // Ejercicios del catálogo público vs. creados por un usuario.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index('primary_muscle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
