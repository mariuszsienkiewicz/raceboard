<?php

declare(strict_types=1);

namespace App\Review\Domain\Service;

use App\Shared\Domain\Model\RaceId;

interface RaceExistenceCheckerInterface
{
    public function exists(RaceId $raceId): bool;
}
