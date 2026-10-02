<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BodyWeightController;
use App\Http\Controllers\Api\EnergyController;
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\FoodController;
use App\Http\Controllers\Api\FoodLogController;
use App\Http\Controllers\Api\MuscleController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoutineController;
use App\Http\Controllers\Api\TrainingAdviceController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register'])->middleware('throttle:auth');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:auth');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Perfil y composición corporal
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'upsert']);
    Route::get('weight', [BodyWeightController::class, 'index']);
    Route::post('weight', [BodyWeightController::class, 'store']);
    Route::delete('weight/{weight}', [BodyWeightController::class, 'destroy']);

    // Gasto energético (BMR/TDEE/objetivo) — cálculo determinista, sin IA
    Route::get('energy', [EnergyController::class, 'show']);

    // Alimentos + diario
    Route::get('foods/search', [FoodController::class, 'search']);
    Route::get('foods/lookups/{lookup}', [FoodController::class, 'lookup']);
    Route::get('foods/{food}', [FoodController::class, 'show']);
    Route::get('diary', [FoodLogController::class, 'index']);
    Route::post('diary', [FoodLogController::class, 'store']);
    Route::delete('diary/{entry}', [FoodLogController::class, 'destroy']);

    // Catálogo de entrenamiento
    Route::get('muscles', [MuscleController::class, 'index']);
    Route::get('exercises', [ExerciseController::class, 'index']);

    // Rutinas (armadas por el usuario) + consejo de carga de la IA
    Route::apiResource('routines', RoutineController::class);
    Route::post('routines/{routine}/advice', [TrainingAdviceController::class, 'store'])
        ->middleware('throttle:ai');
});
