<?php

namespace App\Http\Resources;

use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Food */
class FoodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'barcode' => $this->barcode,
            'source' => $this->source,
            'is_verified' => $this->isVerified(),
            'per_100g' => [
                'kcal' => $this->kcal,
                'protein_g' => $this->protein_g,
                'carb_g' => $this->carb_g,
                'fat_g' => $this->fat_g,
                'fiber_g' => $this->fiber_g,
                'sugar_g' => $this->sugar_g,
                'sat_fat_g' => $this->sat_fat_g,
                'sodium_mg' => $this->sodium_mg,
            ],
            'portions' => $this->whenLoaded('portions', fn () => $this->portions->map(fn ($p) => [
                'id' => $p->id,
                'label' => $p->label,
                'grams' => $p->grams,
                'is_default' => $p->is_default,
            ])),
        ];
    }
}
