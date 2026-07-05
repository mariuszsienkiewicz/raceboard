<?php

declare(strict_types=1);

namespace App\RaceCatalog\Domain\Event;

use App\RaceCatalog\Domain\Model\RaceId;

final readonly class RacesGeocoded
{
    /** @param list<RaceId> $raceIds */
    public function __construct(
        public array $raceIds,
    ) {
    }
}
