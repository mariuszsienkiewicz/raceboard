<?php

declare(strict_types=1);

namespace App\RaceCatalog\Application\Handler;

use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\RaceCatalog\Domain\Service\RaceRatingProviderInterface;
use App\Review\Domain\Event\ReviewAdded;
use App\Review\Domain\Event\ReviewRemoved;
use App\Shared\Domain\Model\RaceId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final class RecalculateRaceAverageRatingHandler
{
    public function __construct(
        private RaceRatingProviderInterface $raceRatingProvider,
        private RaceRepositoryInterface $raceRepository,
    ) {
    }

    #[AsMessageHandler]
    public function onReviewAdded(ReviewAdded $event): void
    {
        $this->recalculate($event->raceId);
    }

    #[AsMessageHandler]
    public function onReviewRemoved(ReviewRemoved $event): void
    {
        $this->recalculate($event->raceId);
    }

    private function recalculate(RaceId $raceId): void
    {
        $averageRating = $this->raceRatingProvider->getAverageRating($raceId);
        $race = $this->raceRepository->findById($raceId);
        if (null === $race) {
            return;
        }

        $race->updateAverageRating($averageRating);
        $this->raceRepository->save($race);
    }
}
