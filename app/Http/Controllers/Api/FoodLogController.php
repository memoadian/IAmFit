<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFoodLogRequest;
use App\Models\Food;
use App\Models\FoodLogEntry;
use App\Models\FoodPortion;
use App\Services\Nutrition\EnergyCalculator;
use Illuminate\Http\Request;

class FoodLogController extends Controller
{
    public function index(Request $request, EnergyCalculator $calculator)
    {
        $date = $request->date('date') ?? today();

        $entries = $request->user()->foodLogEntries()
            ->with('food:id,name,brand,source')
            ->whereDate('consumed_on', $date)
            ->orderBy('created_at')
            ->get();

        $totals = [
            'kcal' => round($entries->sum('kcal'), 1),
            'protein_g' => round($entries->sum('protein_g'), 1),
            'carb_g' => round($entries->sum('carb_g'), 1),
            'fat_g' => round($entries->sum('fat_g'), 1),
        ];

        $target = null;
        $profile = $request->user()->profile;
        $weightKg = $request->user()->latestWeightKg();
        if ($profile && $weightKg !== null) {
            $summary = $calculator->summary($profile, $weightKg);
            $target = ['kcal' => $summary['target_kcal'], 'macros' => $summary['macros']];
        }

        return response()->json([
            'date' => $date->toDateString(),
            'entries' => $entries->groupBy('meal'),
            'totals' => $totals,
            'target' => $target,
        ]);
    }

    public function store(StoreFoodLogRequest $request)
    {
        $data = $request->validated();
        $food = Food::findOrFail($data['food_id']);
        $quantity = (float) ($data['quantity'] ?? 1);

        $portion = null;
        if (! empty($data['food_portion_id'])) {
            $portion = FoodPortion::where('food_id', $food->id)
                ->findOrFail($data['food_portion_id']);
            $grams = $portion->grams * $quantity;
        } else {
            $grams = (float) $data['grams'];
        }

        $nutrients = $food->nutrientsForGrams($grams);

        $entry = $request->user()->foodLogEntries()->create([
            'food_id' => $food->id,
            'food_portion_id' => $portion?->id,
            'meal' => $data['meal'],
            'consumed_on' => $data['consumed_on'] ?? today(),
            'quantity' => $quantity,
            'grams' => round($grams, 2),
            ...$nutrients,
        ]);

        return response()->json(['data' => $entry->load('food:id,name,brand,source')], 201);
    }

    public function destroy(Request $request, FoodLogEntry $entry)
    {
        abort_unless($entry->user_id === $request->user()->id, 403);

        $entry->delete();

        return response()->noContent();
    }
}
