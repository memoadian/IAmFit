<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Nutrition\EnergyCalculator;
use Illuminate\Http\Request;

class EnergyController extends Controller
{
    public function show(Request $request, EnergyCalculator $calculator)
    {
        $user = $request->user();
        $profile = $user->profile;
        $weightKg = $user->latestWeightKg();

        if (! $profile || $weightKg === null) {
            return response()->json([
                'message' => 'Completa tu perfil (sexo, fecha de nacimiento, estatura) y registra tu peso para calcular tu gasto energético.',
            ], 422);
        }

        return response()->json($calculator->summary($profile, $weightKg));
    }
}
