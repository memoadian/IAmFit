<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Time\LocalDay;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Racha semanal para la pantalla Progreso (P1-12). Un día cuenta como completado
 * si el usuario registró al menos una comida o un entrenamiento *en su día local*.
 * Deriva de `food_log_entries` + `workout_sessions`; no necesita tabla propia.
 */
class ProgressController extends Controller
{
    public function streak(Request $request)
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'timezone' => ['nullable', 'timezone'],
        ]);

        $user = $request->user();
        $timezone = LocalDay::timezone($user, $request->query('timezone'));

        $anchor = $request->query('date')
            ? CarbonImmutable::parse($request->query('date'), $timezone)->startOfDay()
            : LocalDay::today($user, $request->query('timezone'));

        $start = $anchor->subDays(6);

        $foodDays = $user->foodLogEntries()
            ->whereBetween('consumed_on', [$start->toDateString(), $anchor->toDateString()])
            ->pluck('consumed_on')
            ->map(fn ($date) => $date->toDateString());

        $workoutDays = $user->workoutSessions()
            ->whereBetween('performed_at', [$start->utc(), $anchor->endOfDay()->utc()])
            ->pluck('performed_at')
            ->map(fn ($at) => $at->setTimezone($timezone)->toDateString());

        $completed = $foodDays->merge($workoutDays)->unique()->flip();

        $days = collect(range(6, 0))->map(function (int $offset) use ($anchor, $completed) {
            $date = $anchor->subDays($offset)->toDateString();

            return ['date' => $date, 'completed' => $completed->has($date)];
        })->values();

        $currentStreak = 0;
        $cursor = $anchor;
        while ($completed->has($cursor->toDateString())) {
            $currentStreak++;
            $cursor = $cursor->subDay();
        }

        return response()->json([
            'data' => [
                'timezone' => $timezone,
                'days' => $days,
                'current_streak' => $currentStreak,
            ],
        ]);
    }
}
