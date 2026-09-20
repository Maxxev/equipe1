<?php

namespace Tests\Feature;

use Tests\TestCase;

class ConversionControllerTest extends TestCase
{
    // —— Tests distance —————————————————————————————————————————————————————————————

    public function test_conversion_distance_retourne_le_bon_resultat(): void
    {
        $response = $this->postJson('/api/conversion/distance', [
            'valeur' => 10,
            'direction' => 'imperial_vers_metrique',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['resultat' => 3.05]);
    }

    public function test_conversion_distance_necessite_valeur(): void
    {
        $response = $this->postJson('/api/conversion/distance', [
            'direction' => 'imperial_vers_metrique',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('valeur');
    }

    public function test_conversion_distance_necessite_direction_valide(): void
    {
        $response = $this->postJson('/api/conversion/distance', [
            'valeur' => 10,
            'direction' => 'direction_inconnue',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('direction');
    }

    public function test_conversion_distance_negative_retourne_erreur_metier(): void
    {
        $response = $this->postJson('/api/conversion/distance', [
            'valeur' => -10,
            'direction' => 'imperial_vers_metrique',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'La distance ne peut pas être négative.']);
    }

    // —— Tests vitesse —————————————————————————————————————————————————————————————

    public function test_conversion_vitesse_retourne_le_bon_resultat(): void
    {
        $response = $this->postJson('/api/conversion/vitesse', [
            'valeur' => 100,
            'direction' => 'kmh_vers_mph',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['resultat' => 62.14]);
    }

    public function test_conversion_vitesse_negative_retourne_erreur_metier(): void
    {
        $response = $this->postJson('/api/conversion/vitesse', [
            'valeur' => -100,
            'direction' => 'kmh_vers_mph',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'La vitesse ne peut pas être négative.']);
    }

    public function test_conversion_vitesse_necessite_valeur_numerique(): void
    {
        $response = $this->postJson('/api/conversion/vitesse', [
            'valeur' => 'abc',
            'direction' => 'kmh_vers_mph',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('valeur');
    }

    // —— Tests température —————————————————————————————————————————————————————————————

    public function test_conversion_temperature_retourne_le_bon_resultat(): void
    {
        $response = $this->postJson('/api/conversion/temperature', [
            'valeur' => 100,
            'direction' => 'celsius_vers_fahrenheit',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['resultat' => 212.0]);
    }

    public function test_conversion_temperature_sous_zero_absolu_retourne_erreur_metier(): void
    {
        $response = $this->postJson('/api/conversion/temperature', [
            'valeur' => -300,
            'direction' => 'celsius_vers_fahrenheit',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'La température ne peut pas être inférieure au zéro absolu (-273.15 °C).']);
    }

    public function test_conversion_temperature_necessite_direction_valide(): void
    {
        $response = $this->postJson('/api/conversion/temperature', [
            'valeur' => 100,
            'direction' => 'direction_inconnue',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('direction');
    }
}
