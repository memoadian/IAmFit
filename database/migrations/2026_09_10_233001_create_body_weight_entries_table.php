<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_weight_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight_kg', 5, 2);
            $table->date('measured_on');
            $table->enum('source', ['manual', 'scale', 'import'])->default('manual');
            $table->string('note')->nullable();
            $table->timestamps();

            // Un peso por día por usuario (el último registro del día gana).
            $table->unique(['user_id', 'measured_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('body_weight_entries');
    }
};
