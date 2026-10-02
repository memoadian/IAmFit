<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // De qué día de rutina salió (opcional: puede ser un entreno suelto).
            $table->foreignId('routine_day_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('performed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sessions');
    }
};
