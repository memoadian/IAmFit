<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AiException;
use App\Http\Controllers\Controller;
use App\Models\Routine;
use App\Services\Training\TrainingAdviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class TrainingAdviceController extends Controller
{
    public function store(Request $request, Routine $routine, TrainingAdviceService $service)
    {
        abort_unless($routine->user_id === $request->user()->id, 403);

        try {
            $advice = $service->forRoutine($request->user(), $routine);
        } catch (AiException $e) {
            report($e);

            return response()->json(['message' => $e->userMessage()], $e->httpStatus());
        } catch (Throwable $e) {
            Log::error('training_advice.unexpected', ['exception' => $e->getMessage()]);

            return response()->json(['message' => 'Ocurrió un error inesperado. Intenta de nuevo.'], 500);
        }

        return response()->json([
            'advice' => $advice->advice,
            'generated_at' => $advice->created_at,
        ]);
    }
}
