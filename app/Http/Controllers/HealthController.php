<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Point de contrôle de santé de l'application.
 *
 * Appelé par :
 *  - le smoke test du pipeline GitHub Actions, juste après chaque déploiement ;
 *  - la supervision externe (UptimeRobot, cron...) ;
 *  - nous, quand on veut savoir en 2 secondes si le serveur va bien.
 *
 * Retourne 200 si tout va bien, 503 sinon. Le code HTTP est la partie
 * importante : c'est lui que le pipeline et les superviseurs regardent.
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->verifierBaseDeDonnees(),
            'cache'    => $this->verifierCache(),
            'storage'  => $this->verifierStockage(),
        ];

        // L'application est saine si AUCUNE vérification n'est en échec.
        $saine = ! in_array('error', array_column($checks, 'status'), true);

        return response()->json([
            'status'      => $saine ? 'ok' : 'error',
            'application' => config('app.name'),
            'environment' => config('app.env'),
            // Le SHA du commit déployé : écrit par scripts/deploy.sh.
            // C'est ce qui permet au smoke test de vérifier que c'est bien
            // LE commit qu'on vient de merger qui est en ligne.
            'version'     => $this->versionDeployee(),
            'deployed_at' => $this->dateDeDeploiement(),
            'checks'      => $checks,
            'timestamp'   => now()->toIso8601String(),
        ], $saine ? 200 : 503);
    }

    /** Une requête triviale qui prouve que la connexion et les identifiants sont bons. */
    private function verifierBaseDeDonnees(): array
    {
        $debut = microtime(true);

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            return ['status' => 'ok', 'latency_ms' => $this->ecoule($debut)];
        } catch (Throwable $e) {
            // On journalise le détail côté serveur mais on ne l'expose PAS
            // dans la réponse : un message d'erreur PDO peut contenir
            // le nom d'utilisateur, l'hôte, parfois plus.
            report($e);

            return ['status' => 'error', 'latency_ms' => $this->ecoule($debut)];
        }
    }

    /** Écrit puis relit une clé : prouve que Redis (ou le driver choisi) répond. */
    private function verifierCache(): array
    {
        $debut = microtime(true);

        try {
            $cle = 'health:'.bin2hex(random_bytes(4));
            Cache::put($cle, 'ping', 10);
            $ok = Cache::get($cle) === 'ping';
            Cache::forget($cle);

            return ['status' => $ok ? 'ok' : 'error', 'latency_ms' => $this->ecoule($debut)];
        } catch (Throwable $e) {
            report($e);

            return ['status' => 'error', 'latency_ms' => $this->ecoule($debut)];
        }
    }

    /** Vérifie que storage/ est bien accessible en écriture (erreur de permissions n°1). */
    private function verifierStockage(): array
    {
        $debut = microtime(true);

        try {
            $fichier = 'health-check.txt';
            Storage::disk('local')->put($fichier, (string) time());
            $ok = Storage::disk('local')->exists($fichier);
            Storage::disk('local')->delete($fichier);

            return ['status' => $ok ? 'ok' : 'error', 'latency_ms' => $this->ecoule($debut)];
        } catch (Throwable $e) {
            report($e);

            return ['status' => 'error', 'latency_ms' => $this->ecoule($debut)];
        }
    }

    private function versionDeployee(): ?string
    {
        $chemin = base_path('VERSION');

        return is_readable($chemin) ? trim((string) file_get_contents($chemin)) : null;
    }

    private function dateDeDeploiement(): ?string
    {
        $chemin = base_path('DEPLOYED_AT');

        return is_readable($chemin) ? trim((string) file_get_contents($chemin)) : null;
    }

    private function ecoule(float $debut): float
    {
        return round((microtime(true) - $debut) * 1000, 2);
    }
}
