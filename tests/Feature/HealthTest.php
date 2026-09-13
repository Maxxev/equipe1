<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HealthTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_la_route_de_sante_repond_200(): void
    {
        $reponse = $this->get('/health');

        $reponse->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['status', 'version', 'checks' => ['database']]);
    }
}
