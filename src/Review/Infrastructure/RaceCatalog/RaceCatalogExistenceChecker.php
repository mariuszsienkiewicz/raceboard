<?php

declare(strict_types=1);

namespace App\Review\Infrastructure\RaceCatalog;

use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Review\Domain\Service\RaceExistenceCheckerInterface;
use App\Shared\Domain\Model\RaceId;

final readonly class RaceCatalogExistenceChecker implements RaceExistenceCheckerInterface
{
    public function __construct(
        private RaceRepositoryInterface $repository,
    ) {
    }

    public function exists(RaceId $raceId): bool
    {
        return $this->repository->exists($raceId);
    }
}
