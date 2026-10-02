<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FoodResource;
use App\Models\Food;
use App\Services\Nutrition\FoodResolver;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    /**
     * Busca un alimento. Si no está en la BD, encola el enriquecimiento
     * (Open Food Facts → USDA → IA) y responde `status: "pending"` con un
     * `lookup_id` para consultar después.
     */
    public function search(Request $request, FoodResolver $resolver)
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        $result = $resolver->resolve($data['q'], $request->user());

        if ($result['status'] === 'found') {
            return response()->json([
                'status' => 'found',
                'food' => new FoodResource($result['food']->load('portions')),
            ]);
        }

        return response()->json([
            'status' => 'pending',
            'lookup_id' => $result['lookup_id'],
            'message' => 'Estamos buscando ese alimento. Vuelve a consultar en unos segundos.',
        ], 202);
    }

    public function lookup(Request $request, FoodResolver $resolver, int $lookup)
    {
        $record = $resolver->lookupStatus($lookup);

        abort_if($record === null, 404);

        return response()->json([
            'status' => $record->status,
            'resolved_by' => $record->resolved_by,
            'food' => $record->food
                ? new FoodResource($record->food->load('portions'))
                : null,
        ]);
    }

    public function show(Food $food)
    {
        return new FoodResource($food->load('portions'));
    }
}
