<?php

namespace App\Http\Controllers;

use App\Services\ConversionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ConversionController extends Controller
{
    public function distance(Request $request, ConversionService $conversion): JsonResponse
    {
        $validated = $request->validate([
            'valeur' => 'required|numeric',
            'direction' => 'required|in:imperial_vers_metrique,metrique_vers_imperial',
        ]);

        try {
            $resultat = $conversion->convertirDistance((float) $validated['valeur'], $validated['direction']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['resultat' => $resultat]);
    }

    public function vitesse(Request $request, ConversionService $conversion): JsonResponse
    {
        $validated = $request->validate([
            'valeur' => 'required|numeric',
            'direction' => 'required|in:kmh_vers_mph,mph_vers_kmh',
        ]);

        try {
            $resultat = $conversion->convertirVitesse((float) $validated['valeur'], $validated['direction']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['resultat' => $resultat]);
    }

    public function temperature(Request $request, ConversionService $conversion): JsonResponse
    {
        $validated = $request->validate([
            'valeur' => 'required|numeric',
            'direction' => 'required|in:celsius_vers_fahrenheit,fahrenheit_vers_celsius',
        ]);

        try {
            $resultat = $conversion->convertirTemperature((float) $validated['valeur'], $validated['direction']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['resultat' => $resultat]);
    }
}
