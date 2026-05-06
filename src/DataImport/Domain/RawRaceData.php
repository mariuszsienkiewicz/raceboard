<?php

declare(strict_types=1);

namespace App\DataImport\Domain;

class RawRaceData
{
    /**
     * @param list<array{name: string, lengthInKm: float, priceInPln: float|null}> $distances
     */
    public function __construct(
        public readonly string $name,
        public readonly string $date,
        public readonly string $city,
        public readonly string $voivodeship,
        public readonly array $distances,
        public readonly string $sourceUrl,
        public readonly ?string $registrationUrl,
    ) {
    }
}
