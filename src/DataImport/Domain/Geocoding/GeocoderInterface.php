<?php

declare(strict_types=1);

namespace App\DataImport\Domain\Geocoding;

interface GeocoderInterface
{
    /** @return array{lat: float, lng: float}|null */
    public function geocode(string $city): ?array;

    /**
     * @param list<string> $cities
     *
     * @return array<string, array{lat: float, lng: float}> city name => coordinates
     */
    public function geocodeMany(array $cities): array;
}
