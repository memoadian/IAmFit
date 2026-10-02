<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BodyWeightEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BodyWeightController extends Controller
{
    public function index(Request $request)
    {
        $entries = $request->user()->bodyWeightEntries()
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
            'source' => ['nullable', Rule::in(BodyWeightEntry::SOURCES)],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $entry = $request->user()->bodyWeightEntries()->updateOrCreate(
            ['measured_on' => $data['measured_on'] ?? today()],
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
