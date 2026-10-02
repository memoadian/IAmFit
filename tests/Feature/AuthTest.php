<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_gets_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Memo',
            'email' => 'memo@example.com',
            'password' => 'password123',
            'device_name' => 'pixel',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'has_profile'], 'token'])
            ->assertJsonPath('user.has_profile', false);

        $this->assertDatabaseHas('users', ['email' => 'memo@example.com']);
    }

    public function test_login_rejects_bad_credentials(): void
    {
        User::factory()->create(['email' => 'memo@example.com', 'password' => 'password123']);

        $this->postJson('/api/login', ['email' => 'memo@example.com', 'password' => 'wrong'])
            ->assertStatus(422);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();

        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);
    }
}
