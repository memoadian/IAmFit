<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Muscle;

class MuscleController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Muscle::orderBy('group')->orderBy('name')->get(),
        ]);
    }
}
