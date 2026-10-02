<?php

namespace Tests\Unit;

use App\Models\Profile;
use App\Services\Nutrition\EnergyCalculator;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EnergyCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-06-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function profile(array $overrides = []): Profile
    {
        return (new Profile)->forceFill(array_merge([
            'sex' => 'male',
            'birthdate' => Carbon::parse('1996-06-15'), // exactamente 30 años
            'height_cm' => 178,
            'activity_level' => 'moderate',
            'goal' => 'maintain',
        ], $overrides));
    }

    public function test_bmr_matches_mifflin_st_jeor_for_a_man(): void
    {
        // 10*80 + 6.25*178 - 5*30 + 5 = 1767.5 -> 1768
        $bmr = (new EnergyCalculator)->bmr($this->profile(), 80);

        $this->assertEqualsWithDelta(1768, $bmr, 1);
    }

    public function test_bmr_uses_the_female_offset(): void
    {
        // 10*60 + 6.25*165 - 5*25 - 161 = 1345.25 -> 1345
        $bmr = (new EnergyCalculator)->bmr(
            $this->profile([
                'sex' => 'female',
                'birthdate' => Carbon::parse('2001-06-15'),
                'height_cm' => 165,
            ]),
            60,
        );

        $this->assertEqualsWithDelta(1345, $bmr, 1);
    }

    public function test_cut_goal_subtracts_a_deficit_from_tdee(): void
    {
        $calc = new EnergyCalculator;
        $profile = $this->profile(['goal' => 'lose', 'goal_rate_kg_per_week' => -0.5]);

        $summary = $calc->summary($profile, 80);

        $this->assertEqualsWithDelta($summary['tdee'] - 550, $summary['target_kcal'], 2);
        $this->assertSame('lose', $summary['goal']);
        $this->assertGreaterThan(0, $summary['macros']['protein_g']);
    }
}
