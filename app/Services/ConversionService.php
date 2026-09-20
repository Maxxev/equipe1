<?php

namespace App\Services;

use InvalidArgumentException;

class ConversionService
{
    private const PIEDS_VERS_METRES = 0.3048;

    private const KMH_VERS_MPH = 0.621371;

    private const ZERO_ABSOLU_CELSIUS = -273.15;

    private const ZERO_ABSOLU_FAHRENHEIT = -459.67;

    /**
     * Convertit une distance entre le système impérial (pieds) et métrique (mètres).
     *
     * @throws InvalidArgumentException si la distance est négative ou si le sens est invalide
     */
    public function convertirDistance(float $valeur, string $direction): float
    {
        if ($valeur < 0) {
            throw new InvalidArgumentException('La distance ne peut pas être négative.');
        }

        return match ($direction) {
            'imperial_vers_metrique' => round($valeur * self::PIEDS_VERS_METRES, 2),
            'metrique_vers_imperial' => round($valeur / self::PIEDS_VERS_METRES, 2),
            default => throw new InvalidArgumentException('Le sens de conversion de distance est invalide.'),
        };
    }

    /**
     * Convertit une vitesse entre km/h et mph.
     *
     * @throws InvalidArgumentException si la vitesse est négative ou si le sens est invalide
     */
    public function convertirVitesse(float $valeur, string $direction): float
    {
        if ($valeur < 0) {
            throw new InvalidArgumentException('La vitesse ne peut pas être négative.');
        }

        return match ($direction) {
            'kmh_vers_mph' => round($valeur * self::KMH_VERS_MPH, 2),
            'mph_vers_kmh' => round($valeur / self::KMH_VERS_MPH, 2),
            default => throw new InvalidArgumentException('Le sens de conversion de vitesse est invalide.'),
        };
    }

    /**
     * Convertit une température entre Celsius et Fahrenheit.
     *
     * @throws InvalidArgumentException si la température est sous le zéro absolu ou si le sens est invalide
     */
    public function convertirTemperature(float $valeur, string $direction): float
    {
        return match ($direction) {
            'celsius_vers_fahrenheit' => $this->celsiusVersFahrenheit($valeur),
            'fahrenheit_vers_celsius' => $this->fahrenheitVersCelsius($valeur),
            default => throw new InvalidArgumentException('Le sens de conversion de température est invalide.'),
        };
    }

    private function celsiusVersFahrenheit(float $celsius): float
    {
        if ($celsius < self::ZERO_ABSOLU_CELSIUS) {
            throw new InvalidArgumentException('La température ne peut pas être inférieure au zéro absolu (-273.15 °C).');
        }

        return round($celsius * 9 / 5 + 32, 2);
    }

    private function fahrenheitVersCelsius(float $fahrenheit): float
    {
        if ($fahrenheit < self::ZERO_ABSOLU_FAHRENHEIT) {
            throw new InvalidArgumentException('La température ne peut pas être inférieure au zéro absolu (-459.67 °F).');
        }

        return round(($fahrenheit - 32) * 5 / 9, 2);
    }
}
