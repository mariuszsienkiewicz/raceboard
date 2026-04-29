<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataImport\Domain;

use App\DataImport\Domain\RawRaceData;
use PHPUnit\Framework\TestCase;

class RawRaceDataTest extends TestCase
{
    public function testRawRaceDataHasAllPropertiesSetOnConstruction(): void
    {
        $name = 'Test Race';
        $date = '2024-09-15';
        $city = 'Test City';
        $voivodeship = 'Test Voivodeship';
        $distances = [
            ['name' => 'Maraton', 'lengthInKm' => 42.195, 'priceInPln' => 150.0],
            ['name' => 'Półmaraton', 'lengthInKm' => 21.0975, 'priceInPln' => 100.0],
        ];
        $sourceUrl = 'https://example.com/race';
        $registrationUrl = 'https://example.com/register';

        $rawRaceData = new RawRaceData(
            $name,
            $date,
            $city,
            $voivodeship,
            $distances,
            $sourceUrl,
            $registrationUrl
        );

        $this->assertSame($name, $rawRaceData->name);
        $this->assertSame($date, $rawRaceData->date);
        $this->assertSame($city, $rawRaceData->city);
        $this->assertSame($voivodeship, $rawRaceData->voivodeship);
        $this->assertSame($distances, $rawRaceData->distances);
        $this->assertSame($sourceUrl, $rawRaceData->sourceUrl);
        $this->assertSame($registrationUrl, $rawRaceData->registrationUrl);
    }

    public function testRegistrationUrlCanBeNull(): void
    {
        $rawRaceData = new RawRaceData(
            'Test Race',
            '2026-09-15',
            'Warsaw',
            'mazowieckie',
            [],
            'https://example.com/race',
            null,
        );

        $this->assertNull($rawRaceData->registrationUrl);
        $this->assertSame([], $rawRaceData->distances);
    }
}