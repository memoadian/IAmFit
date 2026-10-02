<?php

namespace App\Http\Controllers\Api;

use App\Enums\WeightSource;
use App\Http\Controllers\Controller;
use App\Models\BodyWeightEntry;
use App\Services\Time\LocalDay;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BodyWeightController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'days' => ['nullable', 'integer', 'between:1,3650'],
        ]);

        $entries = $request->user()->bodyWeightEntries()
            ->when($request->integer('days'), fn ($q, $days) => $q->where(
                'measured_on',
                '>=',
                LocalDay::today($request->user(), $request->query('timezone'))->subDays($days)->toDateString(),
            ))
            ->orderByDesc('measured_on')
            ->limit(365)
            ->get();

        return response()->json(['data' => $entries]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'weight_kg' => ['required', 'numeric', 'min:25', 'max:400'],
            'measured_on' => ['nullable', 'date', 'before_or_equal:today'],
            'timezone' => ['nullable', 'timezone'],
            'source' => ['nullable', Rule::enum(WeightSource::class)],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $entry = $request->user()->bodyWeightEntries()->updateOrCreate(
            ['measured_on' => $data['measured_on'] ?? LocalDay::today($request->user(), $data['timezone'] ?? null)],
            [
                'weight_kg' => $data['weight_kg'],
                'source' => $data['source'] ?? 'manual',
                'note' => $data['note'] ?? null,
            ],
        );

        return response()->json(['data' => $entry], 201);
    }

    public function destroy(Request $request, BodyWeightEntry $weight)
    {
        abort_unless($weight->user_id === $request->user()->id, 403);

        $weight->delete();

        return response()->noContent();
    }
}
