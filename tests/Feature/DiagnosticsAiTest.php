<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiagnosticsAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_diagnostics_pings_the_ai_provider(): void
    {
        config(['services.groq.api_key' => 'test-key']);

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [[
                    'message' => ['content' => 'pong'],
                    'finish_reason' => 'stop',
                ]],
                'usage' => ['prompt_tokens' => 5, 'completion_tokens' => 1],
            ], 200),
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson('/api/diagnostics/ai')
            ->assertOk()
            ->assertJsonPath('data.provider', 'groq')
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.reply', 'pong');
    }

    public function test_diagnostics_surfaces_rate_limit_as_a_safe_error(): void
    {
        config(['services.groq.api_key' => 'test-key']);

        Http::fake([
            'api.groq.com/*' => Http::response(['error' => 'rate'], 429, ['Retry-After' => '3']),
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson('/api/diagnostics/ai')
            ->assertStatus(429)
            ->assertJsonStructure(['message', 'errors' => ['ai']]);
    }
}
