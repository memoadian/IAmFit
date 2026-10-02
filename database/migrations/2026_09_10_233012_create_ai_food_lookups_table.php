<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_food_lookups', function (Blueprint $table) {
            $table->id();
            // Texto normalizado que buscó el usuario ("tacos al pastor").
            $table->string('query');
            $table->string('query_hash', 64)->index();
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            // Qué fuente resolvió la búsqueda al final.
            $table->enum('resolved_by', ['off', 'usda', 'ai'])->nullable();
            $table->foreignId('food_id')->nullable()->constrained('foods')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            // Respuesta cruda del proveedor, para depurar.
            $table->jsonb('raw')->nullable();
            $table->text('error')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_food_lookups');
    }
};
