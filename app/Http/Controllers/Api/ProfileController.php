<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $profile = $request->user()->profile;

        return response()->json([
            'profile' => $profile,
            'latest_weight_kg' => $request->user()->latestWeightKg(),
        ]);
    }

    public function upsert(ProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $profile = $user->profile()->updateOrCreate([], collect($data)->except('weight_kg')->all());

        // Sembrar el primer registro de peso si el usuario aún no tiene ninguno.
        if (! empty($data['weight_kg']) && $user->bodyWeightEntries()->doesntExist()) {
            $user->bodyWeightEntries()->create([
                'weight_kg' => $data['weight_kg'],
                'measured_on' => today(),
                'source' => 'manual',
            ]);
        }

        return response()->json([
            'profile' => $profile->fresh(),
            'latest_weight_kg' => $user->latestWeightKg(),
        ]);
    }
}
