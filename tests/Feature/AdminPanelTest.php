<?php

namespace Tests\Feature;

use App\Filament\Resources\AiFoodLookups\Pages\ListAiFoodLookups;
use App\Filament\Resources\Exercises\Pages\ListExercises;
use App\Filament\Resources\Food\Pages\EditFood;
use App\Filament\Resources\Food\Pages\ListFood;
use App\Filament\Resources\Muscles\Pages\ListMuscles;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Food;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guests_are_redirected_to_the_panel_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admins_can_access_the_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_resource_pages_render(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $food = Food::create([
            'name' => 'Pechuga de pollo asada',
            'source' => 'ai',
            'kcal' => 165, 'protein_g' => 31, 'carb_g' => 0, 'fat_g' => 3.6,
        ]);

        Livewire::test(ListFood::class)->assertOk();
        Livewire::test(EditFood::class, ['record' => $food->getRouteKey()])->assertOk();
        Livewire::test(ListAiFoodLookups::class)->assertOk();
        Livewire::test(ListExercises::class)->assertOk();
        Livewire::test(ListMuscles::class)->assertOk();
        Livewire::test(ListUsers::class)->assertOk();
    }

    public function test_the_verify_action_marks_an_ai_food_as_verified(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $food = Food::create([
            'name' => 'Tlacoyo de haba',
            'source' => 'ai',
            'kcal' => 180, 'protein_g' => 7.5, 'carb_g' => 24, 'fat_g' => 5.5,
            'verified_at' => null,
        ]);

        Livewire::test(ListFood::class)
            ->callTableAction('verify', $food)
            ->assertHasNoTableActionErrors();

        $food->refresh();

        $this->assertNotNull($food->verified_at);
        $this->assertSame($admin->id, $food->verified_by);
    }
}
