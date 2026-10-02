<?php

namespace App\Services\Nutrition\Contracts;

use App\Services\Nutrition\NutritionFacts;

/**
 * Una fuente de datos nutricionales: Open Food Facts, USDA FoodData Central,
 * o la IA como último recurso. El FoodResolver las prueba en orden.
 */
interface NutritionSource
{
    /** Identificador corto, coincide con Food::SOURCES (p. ej. "off"). */
    public function key(): string;

    /** true si está configurada y disponible (p. ej. tiene API key). */
    public function isEnabled(): bool;

    /** Busca por texto libre. Devuelve null si no encuentra nada confiable. */
    public function search(string $query): ?NutritionFacts;

    /** Busca por código de barras. Devuelve null si no aplica o no encuentra. */
    public function byBarcode(string $barcode): ?NutritionFacts;
}
