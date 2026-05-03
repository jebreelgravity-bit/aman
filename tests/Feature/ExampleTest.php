<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that the PWA home route returns a successful response.
     */
    public function test_the_pwa_home_returns_a_successful_response(): void
    {
        $response = $this->get('/app/');
        $response->assertStatus(200);
    }

    /**
     * Test that the API login endpoint exists.
     */
    public function test_api_login_endpoint_exists(): void
    {
        $response = $this->postJson('/api/login', []);
        // Should return 422 (validation error) not 404
        $response->assertStatus(422);
    }
}
