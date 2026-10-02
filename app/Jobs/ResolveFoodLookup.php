<?php

namespace App\Jobs;

use App\Enums\LookupStatus;
use App\Models\AiFoodLookup;
use App\Models\Food;
use App\Services\Nutrition\NutritionFacts;
use App\Services\Nutrition\NutritionLookup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Resuelve una AiFoodLookup pendiente contra las fuentes nutricionales y, si
 * encuentra datos, crea el Food (sin verificar) para que quede en la BD.
 */
class ResolveFoodLookup implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $backoff = 10;

    public function __construct(public int $lookupId) {}

    public function handle(NutritionLookup $lookup): void
    {
        $record = AiFoodLookup::find($this->lookupId);

        if (! $record || $record->status === LookupStatus::Done) {
            return;
        }

        $record->update(['status' => 'processing']);

        $hit = $lookup->search($record->query);

        if ($hit === null) {
            $record->update(['status' => 'failed', 'error' => 'Ninguna fuente devolvió datos.']);

            return;
        }

        /** @var NutritionFacts $facts */
        ['source' => $source, 'facts' => $facts] = $hit;

        $food = DB::transaction(function () use ($facts, $source) {
            $food = Food::create($facts->toFoodAttributes($source));

            $food->portions()->create([
                'label' => '100 g',
                'grams' => 100,
                'is_default' => $facts->portions === [],
            ]);

            foreach ($facts->portions as $i => $portion) {
                $food->portions()->create([
                    'label' => $portion['label'],
                    'grams' => $portion['grams'],
                    'is_default' => $i === 0,
                ]);
            }

            return $food;
        });

        $record->update([
            'status' => 'done',
            'resolved_by' => $source,
            'food_id' => $food->id,
            'raw' => $facts->raw,
            'prompt_tokens' => $facts->raw['tokens'][0] ?? null,
            'completion_tokens' => $facts->raw['tokens'][1] ?? null,
        ]);
    }

    public function failed(Throwable $e): void
    {
        AiFoodLookup::where('id', $this->lookupId)->update([
            'status' => 'failed',
            'error' => Str::limit($e->getMessage(), 1000, ''),
        ]);
    }
}
