<?php

use App\Http\Controllers\CalculateurController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('calculateur');
})->name('calculateur');

Route::post('/api/calculateur/prix-ttc', [CalculateurController::class, 'prixTtc']);
Route::post('/api/calculateur/appliquer-remise', [CalculateurController::class, 'appliquerRemise']);
Route::post('/api/calculateur/respecte-seuil-minimum', [CalculateurController::class, 'respecteSeuilMinimum']);

Route::get('/health', HealthController::class)->name('health');
