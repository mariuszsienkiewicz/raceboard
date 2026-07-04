<?php

declare(strict_types=1);

namespace App\DataImport\Application;

final readonly class GeocodeRacesResult
{
    public function __construct(
        public int $geocodedCount = 0,
        public int $failedCount = 0,
    ) {
    }
}
