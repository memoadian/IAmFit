<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Nutrition\EnergyCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnergyController extends Controller
{
    public function show(Request $request, EnergyCalculator $calculator)
    {
        $user = $request->user();
        $profile = $user->profile;
        $weightKg = $user->latestWeightKg();

        if (! $profile || $weightKg === null) {
            throw ValidationException::withMessages([
                'profile' => 'Completa tu perfil (sexo, fecha de nacimiento, estatura) y registra tu peso para calcular tu gasto energético.',
            ]);
        }

        return response()->json(['data' => $calculator->summary($profile, $weightKg)]);
    }
}
