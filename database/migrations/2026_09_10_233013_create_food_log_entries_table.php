<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_log_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_id')->constrained('foods')->restrictOnDelete();
            $table->foreignId('food_portion_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('meal', ['breakfast', 'lunch', 'dinner', 'snack']);
            $table->date('consumed_on');
            $table->decimal('quantity', 6, 2)->default(1);   // nº de porciones
            $table->decimal('grams', 8, 2);                   // gramos totales resueltos

            // Snapshot de los valores al momento de registrar: si el alimento se
            // corrige después, el historial del usuario no se reescribe solo.
            $table->decimal('kcal', 9, 2);
            $table->decimal('protein_g', 8, 2);
            $table->decimal('carb_g', 8, 2);
            $table->decimal('fat_g', 8, 2);

            $table->timestamps();

            $table->index(['user_id', 'consumed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_log_entries');
    }
};
