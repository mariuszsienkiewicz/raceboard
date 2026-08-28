<?php

declare(strict_types=1);

namespace App\UserProfile\Infrastructure\RaceCatalog;

use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use App\UserProfile\Domain\Service\RaceExistenceCheckerInterface;

final readonly class RaceCatalogExistenceChecker implements RaceExistenceCheckerInterface
{
    public function __construct(
        private RaceRepositoryInterface $raceRepository,
    ) {
    }

    public function exists(RaceId $raceId): bool
    {
        return $this->raceRepository->exists($raceId);
    }
}
