<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * El health check del framework sigue respondiendo (la raíz es del panel).
     */
    public function test_the_framework_health_endpoint_returns_a_successful_response(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }
}
