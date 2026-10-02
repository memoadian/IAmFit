<?php

use App\Services\Nutrition\Sources\AiNutritionSource;
use App\Services\Nutrition\Sources\OpenFoodFactsSource;
use App\Services\Nutrition\Sources\UsdaFdcSource;

return [

    /*
    | Fuentes de datos nutricionales, EN ORDEN DE PREFERENCIA. El FoodResolver
    | prueba una por una y se queda con la primera que devuelva datos válidos.
    | Bases abiertas primero (verificables); la IA al final, como estimación.
    */
    'sources' => [
        OpenFoodFactsSource::class,
        UsdaFdcSource::class,
        AiNutritionSource::class,
    ],

    /*
    | Cuántos días vale una búsqueda con IA fallida antes de reintentarla.
    */
    'failed_lookup_ttl_days' => 7,

];
