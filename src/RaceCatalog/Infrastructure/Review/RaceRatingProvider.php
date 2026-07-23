<?php

declare(strict_types=1);

namespace App\RaceCatalog\Infrastructure\Review;

use App\RaceCatalog\Domain\Service\RaceRatingProviderInterface;
use App\Review\Domain\Repository\ReviewRepositoryInterface;
use App\Shared\Domain\Model\RaceId;

final readonly class RaceRatingProvider implements RaceRatingProviderInterface
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function getAverageRating(RaceId $raceId): ?float
    {
        return $this->repository->getAverageRating($raceId);
    }
}
