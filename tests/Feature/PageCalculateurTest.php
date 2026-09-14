<?php

namespace Tests\Feature;

use Tests\TestCase;

class PageCalculateurTest extends TestCase
{
    public function test_la_page_d_accueil_affiche_le_calculateur(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Calculateur de prix');
    }

    /**
     * La page n'a d'interet que si elle pointe vers les trois routes de l'API.
     * Ce test casse si quelqu'un renomme une route sans toucher a la vue.
     */
    public function test_la_page_reference_les_trois_routes_de_l_api(): void
    {
        $response = $this->get('/');

        $response->assertSee('/api/calculateur/prix-ttc')
            ->assertSee('/api/calculateur/appliquer-remise')
            ->assertSee('/api/calculateur/respecte-seuil-minimum');
    }
}
