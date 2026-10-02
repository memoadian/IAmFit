<?php

namespace App\Http\Controllers\Api;

use App\Contracts\AiChatProvider;
use App\Exceptions\AiException;
use App\Http\Controllers\Controller;

/**
 * Sustituto backend del "Probar conexión" que hacía la app Android contra Groq
 * directamente (P1-11).
 */
class DiagnosticsController extends Controller
{
    public function ai(AiChatProvider $ai)
    {
        try {
            $result = $ai->complete(
                'Eres un servicio de diagnóstico. Responde exactamente con la palabra "pong".',
                'ping',
            );
        } catch (AiException $e) {
            report($e);

            return response()->json([
                'message' => $e->userMessage(),
                'errors' => ['ai' => [$e->userMessage()]],
            ], $e->httpStatus());
        }

        return response()->json([
            'data' => [
                'provider' => $ai->name(),
                'status' => 'ok',
                'reply' => trim($result->content),
            ],
        ]);
    }
}
