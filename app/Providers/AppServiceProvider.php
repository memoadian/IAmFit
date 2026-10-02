<?php

namespace App\Providers;

use App\Contracts\AiChatProvider;
use App\Services\Ai\GroqChatProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Único lugar que sabe qué proveedor de IA se usa. Cambiar a un backend
        // propio / OpenAI / Gemini es escribir una clase que implemente
        // AiChatProvider y apuntar el bind aquí — nada más se toca.
        $this->app->bind(AiChatProvider::class, GroqChatProvider::class);
    }

    public function boot(): void
    {
        // Groq limita por organización (una sola API key), no por usuario, así
        // que este bucket es único y compartido por TODAS las features de IA
        // (enriquecer alimentos, consejo de carga, etc.). ~6/min es margen bajo
        // el techo real de gpt-oss-20b (~8k tokens/min ÷ ~1.5k por llamada).
        RateLimiter::for('ai', function () {
            return Limit::perMinute(6)->by('ai')->response(function () {
                return response()->json([
                    'message' => 'Se alcanzó el límite de solicitudes a la IA. Espera unos segundos e intenta de nuevo.',
                ], 429);
            });
        });

        // Endpoints públicos de auth: por IP.
        RateLimiter::for('auth', function ($request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
