<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained()->cascadeOnDelete();
            // "Día A — Empuje", "Pierna", etc.
            $table->string('label');
            $table->unsignedTinyInteger('position')->default(1);
            $table->timestamps();

            $table->index('routine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_days');
    }
};
