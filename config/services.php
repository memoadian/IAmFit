<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // LLM (Groq — API compatible con OpenAI). Ver App\Contracts\AiChatProvider.
    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
    ],

    // USDA FoodData Central: https://fdc.nal.usda.gov/api-key-signup.html
    'usda' => [
        'api_key' => env('USDA_FDC_API_KEY'),
        'base_url' => env('USDA_FDC_BASE_URL', 'https://api.nal.usda.gov/fdc/v1'),
    ],

    // Open Food Facts (sin key; requiere User-Agent identificable).
    //  - search_url: servicio de búsqueda (search-a-licious), buena relevancia.
    //  - base_url: API de producto por código de barras.
    'openfoodfacts' => [
        'search_url' => env('OFF_SEARCH_URL', 'https://search.openfoodfacts.org'),
        'base_url' => env('OFF_BASE_URL', 'https://world.openfoodfacts.org'),
        'user_agent' => env('OFF_USER_AGENT', 'IAm-fit/0.1'),
    ],

];
