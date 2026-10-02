<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_reports_ok_without_auth(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }
}
