<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_a_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'memo@example.com']);

        $this->postJson('/api/forgot-password', ['email' => 'memo@example.com'])
            ->assertOk()
            ->assertJsonStructure(['data' => ['message']]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_rejects_an_unknown_email(): void
    {
        $this->postJson('/api/forgot-password', ['email' => 'nadie@example.com'])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['email']]);
    }

    public function test_reset_password_changes_the_password_and_revokes_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'memo@example.com',
            'password' => 'oldpassword',
        ]);

        $token = $user->createToken('pixel')->plainTextToken;
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $resetToken = Password::createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $resetToken,
            'email' => 'memo@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk()->assertJsonStructure(['data' => ['message']]);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertNotEmpty($token);
    }
}
