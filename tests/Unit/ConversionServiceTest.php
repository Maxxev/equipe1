<?php

namespace Tests\Unit;

use App\Services\ConversionService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConversionServiceTest extends TestCase
{
    // —— Tests distance —————————————————————————————————————————————————————————————

    public function test_conversion_pieds_vers_metres(): void
    {
        $conversion = new ConversionService;
        $resultat = $conversion->convertirDistance(10, 'imperial_vers_metrique');
        $this->assertEquals(3.05, $resultat);
    }

    public function test_conversion_metres_vers_pieds(): void
    {
        $conversion = new ConversionService;
        $resultat = $conversion->convertirDistance(10, 'metrique_vers_imperial');
        $this->assertEquals(32.81, $resultat);
    }

    public function test_distance_negative_leve_exception(): void
    {
        $conversion = new ConversionService;
        $this->expectException(InvalidArgumentException::class);
        $conversion->convertirDistance(-1, 'imperial_vers_metrique');
    }

    public function test_direction_distance_invalide_leve_exception(): void
    {
        $conversion = new ConversionService;
        $this->expectException(InvalidArgumentException::class);
        $conversion->convertirDistance(10, 'direction_inconnue');
    }

    // —— Tests vitesse —————————————————————————————————————————————————————————————

    public function test_conversion_kmh_vers_mph(): void
    {
        $conversion = new ConversionService;
        $resultat = $conversion->convertirVitesse(100, 'kmh_vers_mph');
        $this->assertEquals(62.14, $resultat);
    }

    public function test_conversion_mph_vers_kmh(): void
    {
        $conversion = new ConversionService;
        $resultat = $conversion->convertirVitesse(100, 'mph_vers_kmh');
        $this->assertEquals(160.93, $resultat);
    }

    public function test_vitesse_negative_leve_exception(): void
    {
        $conversion = new ConversionService;
        $this->expectException(InvalidArgumentException::class);
        $conversion->convertirVitesse(-1, 'kmh_vers_mph');
    }

    public function test_direction_vitesse_invalide_leve_exception(): void
    {
        $conversion = new ConversionService;
        $this->expectException(InvalidArgumentException::class);
        $conversion->convertirVitesse(100, 'direction_inconnue');
    }

    // —— Tests température —————————————————————————————————————————————————————————————

    public function test_conversion_celsius_vers_fahrenheit(): void
    {
        $conversion = new ConversionService;
        $resultat = $conversion->convertirTemperature(100, 'celsius_vers_fahrenheit');
        $this->assertEquals(212.0, $resultat);
    }

    public function test_conversion_fahrenheit_vers_celsius(): void
    {
        $conversion = new ConversionService;
        $resultat = $conversion->convertirTemperature(32, 'fahrenheit_vers_celsius');
        $this->assertEquals(0.0, $resultat);
    }

    public function test_celsius_sous_zero_absolu_leve_exception(): void
    {
        $conversion = new ConversionService;
        $this->expectException(InvalidArgumentException::class);
        $conversion->convertirTemperature(-300, 'celsius_vers_fahrenheit');
    }

    public function test_fahrenheit_sous_zero_absolu_leve_exception(): void
    {
        $conversion = new ConversionService;
        $this->expectException(InvalidArgumentException::class);
        $conversion->convertirTemperature(-500, 'fahrenheit_vers_celsius');
    }

    public function test_direction_temperature_invalide_leve_exception(): void
    {
        $conversion = new ConversionService;
        $this->expectException(InvalidArgumentException::class);
        $conversion->convertirTemperature(100, 'direction_inconnue');
    }
}
