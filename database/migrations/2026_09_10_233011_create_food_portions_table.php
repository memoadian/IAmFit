<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_portions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
            // Etiqueta legible: "1 taza", "1 pieza mediana", "1 rebanada".
            $table->string('label');
            $table->decimal('grams', 8, 2);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('food_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_portions');
    }
};
