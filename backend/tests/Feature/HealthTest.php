<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_reports_service_readiness_without_exposing_secrets(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk()
            ->assertJsonStructure(['status', 'service', 'version', 'time'])
            ->assertJson([
                'status' => 'ok',
                'service' => 'modern-garment-erp-api',
            ]);

        $this->assertStringNotContainsString('password', strtolower($response->getContent()));
    }
}
