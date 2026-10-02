<?php

namespace App\Services\Nutrition;

use App\Enums\LookupStatus;
use App\Jobs\ResolveFoodLookup;
use App\Models\AiFoodLookup;
use App\Models\Food;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Resuelve un alimento a partir de texto libre:
 *
 *   1. Si ya está en la BD, lo devuelve al instante.
 *   2. Si no, encola una búsqueda (Open Food Facts → USDA → IA) y devuelve
 *      "pending". La app vuelve a preguntar en unos segundos.
 *
 * Así la IA "alimenta la BD con sus findings" sin bloquear la petición.
 */
class FoodResolver
{
    /**
     * @return array{status: 'found'|'pending', food: Food|null, lookup_id: int|null}
     */
    public function resolve(string $query, ?User $user = null): array
    {
        $query = trim(preg_replace('/\s+/', ' ', $query));

        $existing = Food::query()->search($query)->first();
        if ($existing) {
            return ['status' => 'found', 'food' => $existing, 'lookup_id' => null];
        }

        $lookup = $this->pendingOrRecentLookup($query, $user);

        if ($lookup->status === LookupStatus::Done && $lookup->food) {
            return ['status' => 'found', 'food' => $lookup->food, 'lookup_id' => $lookup->id];
        }

        return ['status' => 'pending', 'food' => null, 'lookup_id' => $lookup->id];
    }

    public function lookupStatus(int $lookupId): ?AiFoodLookup
    {
        return AiFoodLookup::with('food')->find($lookupId);
    }

    private function pendingOrRecentLookup(string $query, ?User $user): AiFoodLookup
    {
        $hash = AiFoodLookup::hashFor($query);
        $ttlDays = (int) config('nutrition.failed_lookup_ttl_days', 7);

        $recent = AiFoodLookup::query()
            ->where('query_hash', $hash)
            ->where(function ($q) use ($ttlDays) {
                $q->whereIn('status', ['pending', 'processing', 'done'])
                    ->orWhere(fn ($q2) => $q2->where('status', 'failed')
                        ->where('created_at', '>=', Carbon::now()->subDays($ttlDays)));
            })
            ->latest()
            ->first();

        if ($recent) {
            return $recent;
        }

        $lookup = AiFoodLookup::create([
            'query' => $query,
            'query_hash' => $hash,
            'status' => 'pending',
            'requested_by' => $user?->id,
        ]);

        ResolveFoodLookup::dispatch($lookup->id);

        return $lookup;
    }
}
