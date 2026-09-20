<?php

use App\Http\Controllers\CalculateurController;
use App\Http\Controllers\ConversionController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/calculateur/prix-ttc', [CalculateurController::class, 'prixTtc']);
Route::post('/api/calculateur/appliquer-remise', [CalculateurController::class, 'appliquerRemise']);

Route::post('/api/conversion/distance', [ConversionController::class, 'distance']);
Route::post('/api/conversion/vitesse', [ConversionController::class, 'vitesse']);
Route::post('/api/conversion/temperature', [ConversionController::class, 'temperature']);
// Route::get('/health', function () {
//     // Vérifier la connexion à la base de données
//     try {
//         DB::connection()->getPdo();
//         $dbStatus = 'ok';
//     } catch (Exception $e) {
//         $dbStatus = 'error';
//     }
//     $status = $dbStatus === 'ok' ? 'ok' : 'degraded';
//     $httpCode = $status === 'ok' ? 200 : 503;

//     return response()->json([
//         'status' => $status,
//         'database' => $dbStatus,
//         'version' => config('app.version', '1.0.0'),
//     ], $httpCode);
// });

Route::get('/health', HealthController::class)->name('health');
