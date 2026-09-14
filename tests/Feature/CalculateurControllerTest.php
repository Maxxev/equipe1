<?php

namespace Tests\Feature;

use Tests\TestCase;

class CalculateurControllerTest extends TestCase
{
    // —— Tests prixTtc —————————————————————————————————————————————————————————————
    public function test_calcul_prix_ttc_retourne_le_bon_resultat(): void
    {
        // Act: on envoie une vraie requête HTTP POST à la route
        $response = $this->postJson('/api/calculateur/prix-ttc', [
            'prix_ht' => 100,
            'taux_taxe' => 0.15,
        ]);

        // Assert: on vérifie le code HTTP et le contenu JSON exact
        $response->assertStatus(200);
        $response->assertJson(['prix_ttc' => 115.0]);
    }

    public function test_calcul_prix_ttc_necessite_prix_ht(): void
    {
        $response = $this->postJson('/api/calculateur/prix-ttc', [
            'taux_taxe' => 0.15,
        ]);

        // Laravel renvoie 422 + une erreur de validation par champ manquant/invalide
        $response->assertStatus(422)
            ->assertJsonValidationErrors('prix_ht');
    }

    public function test_calcul_prix_ttc_necessite_taux_taxe(): void
    {
        $response = $this->postJson('/api/calculateur/prix-ttc', [
            'prix_ht' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('taux_taxe');
    }

    public function test_calcul_prix_ttc_avec_prix_negatif_retourne_erreur_metier(): void
    {
        // Ici la validation Laravel passe (c'est un nombre valide),
        // mais la règle métier dans CalculateurPrix rejette la valeur négative.
        $response = $this->postJson('/api/calculateur/prix-ttc', [
            'prix_ht' => -100,
            'taux_taxe' => 0.15,
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Le prix hors taxes ne peut pas être négatif.']);
    }

    // —— Tests appliquerRemise —————————————————————————————————————————————————————————————

    public function test_calcul_remise_retourne_le_bon_resultat(): void
    {
        // Act
        $response = $this->postJson('/api/calculateur/appliquer-remise', [
            'prix' => 100,
            'remise_pourcentage' => 20,
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson(['prix_remise' => 80]);
    }

    public function test_calcul_prix_negatif_retourne_erreur_metier(): void
    {
        // Act
        $response = $this->postJson('/api/calculateur/appliquer-remise', [
            'prix' => -100,
            'remise_pourcentage' => 20,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJson(['message' => 'Le prix ne peut pas être négatif.']);
    }

    public function test_calcul_remise_negative_retourne_erreur_metier(): void
    {
        // Act
        $response = $this->postJson('/api/calculateur/appliquer-remise', [
            'prix' => 100,
            'remise_pourcentage' => -20,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJson(['message' => 'La remise ne peut pas être négative.']);
    }

    public function test_calcul_remise_necessite_prix(): void
    {
        // Act
        $response = $this->postJson('/api/calculateur/appliquer-remise', [
            'remise_pourcentage' => 20,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('prix');
    }

    public function test_calcul_remise_necessite_remise(): void
    {
        // Act
        $response = $this->postJson('/api/calculateur/appliquer-remise', [
            'prix' => 100,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('remise_pourcentage');
    }

    public function test_remise_superieure_a_100_donne_prix_de_0(): void
    {
        // Act
        $response = $this->postJson('/api/calculateur/appliquer-remise', [
            'prix' => 100,
            'remise_pourcentage' => 150,
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson(['prix_remise' => 0]);
    }

    // —— Tests respecteSeuilMinimum —————————————————————————————————————————————————————————————

    public function test_seuil_minimum_retourne_vrai_quand_le_prix_atteint_le_seuil(): void
    {
        $response = $this->postJson('/api/calculateur/respecte-seuil-minimum', [
            'prix' => 100,
            'seuil_minimum' => 50,
        ]);

        $response->assertStatus(200)
            ->assertJson(['respecte_seuil_minimum' => true]);
    }

    public function test_seuil_minimum_retourne_faux_quand_le_prix_est_sous_le_seuil(): void
    {
        $response = $this->postJson('/api/calculateur/respecte-seuil-minimum', [
            'prix' => 25,
            'seuil_minimum' => 50,
        ]);

        $response->assertStatus(200)
            ->assertJson(['respecte_seuil_minimum' => false]);
    }

    public function test_seuil_minimum_necessite_prix(): void
    {
        $response = $this->postJson('/api/calculateur/respecte-seuil-minimum', [
            'seuil_minimum' => 50,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('prix');
    }

    public function test_seuil_minimum_necessite_seuil(): void
    {
        $response = $this->postJson('/api/calculateur/respecte-seuil-minimum', [
            'prix' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('seuil_minimum');
    }

    public function test_seuil_minimum_avec_prix_negatif_retourne_erreur_metier(): void
    {
        $response = $this->postJson('/api/calculateur/respecte-seuil-minimum', [
            'prix' => -10,
            'seuil_minimum' => 50,
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Le prix ne peut pas être négatif.']);
    }

    public function test_seuil_minimum_avec_seuil_negatif_retourne_erreur_metier(): void
    {
        $response = $this->postJson('/api/calculateur/respecte-seuil-minimum', [
            'prix' => 100,
            'seuil_minimum' => -50,
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Le seuil minimum ne peut pas être négatif.']);
    }
}
