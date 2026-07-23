<?php

declare(strict_types=1);

namespace App\RaceCatalog\Domain\Service;

use App\Shared\Domain\Model\RaceId;

interface RaceRatingProviderInterface
{
    public function getAverageRating(RaceId $raceId): ?float;
}
