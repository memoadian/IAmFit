<?php

namespace App\Contracts;

use App\Exceptions\AiException;
use App\Services\Ai\AiChatResult;

/**
 * Abstrae la llamada a un proveedor de LLM (Groq, OpenAI, Gemini, Anthropic...)
 * para que los servicios de la app dependan de este contrato y no del proveedor
 * concreto. Cambiar de proveedor = una nueva implementación + rebind en el
 * service provider, sin tocar el resto de la app.
 *
 * El resto de la app depende de esta interfaz, no del proveedor concreto.
 */
interface AiChatProvider
{
    /** Identificador corto del proveedor, sólo para logging (p. ej. "groq"). */
    public function name(): string;

    /**
     * Envía el prompt de sistema + el texto del usuario y devuelve la respuesta
     * ya extraída (texto + tokens consumidos, si el proveedor los reporta).
     *
     * @param  array<string, mixed>|null  $jsonSchema  JSON Schema (estilo structured
     *                                                 outputs de OpenAI/Anthropic) que
     *                                                 la respuesta debe cumplir, o null
     *                                                 para JSON libre.
     *
     * @throws AiException si el proveedor falla (HTTP, timeout, etc.)
     */
    public function complete(string $systemPrompt, string $userText, ?array $jsonSchema = null): AiChatResult;
}
