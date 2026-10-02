<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('barcode')->nullable();

            // De dónde salieron los datos nutricionales.
            $table->enum('source', ['off', 'usda', 'ai', 'manual']);
            // Id del alimento en la fuente externa (code de OFF, fdcId de USDA).
            $table->string('external_id')->nullable();
            // País/mercado de referencia — para el enfoque "mexa/internacional".
            $table->string('locale', 10)->nullable();

            // Todos los valores nutricionales se guardan POR 100 g de porción
            // comestible. Las porciones legibles viven en food_portions.
            $table->decimal('kcal', 9, 2);
            $table->decimal('protein_g', 8, 2)->default(0);
            $table->decimal('carb_g', 8, 2)->default(0);
            $table->decimal('fat_g', 8, 2)->default(0);
            $table->decimal('fiber_g', 8, 2)->nullable();
            $table->decimal('sugar_g', 8, 2)->nullable();
            $table->decimal('sat_fat_g', 8, 2)->nullable();
            $table->decimal('sodium_mg', 9, 2)->nullable();
            // Micronutrientes / campos extra de la fuente, sin esquema fijo.
            $table->jsonb('micros')->nullable();

            // null = dato sin revisar (estimado por IA o importado). La app
            // muestra un badge "estimado" hasta que un admin lo verifica.
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('barcode');
            $table->unique(['source', 'external_id']);
        });

        // Búsqueda por nombre tolerante a acentos/typos.
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE INDEX foods_name_trgm_idx ON foods USING gin (lower(name) gin_trgm_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
