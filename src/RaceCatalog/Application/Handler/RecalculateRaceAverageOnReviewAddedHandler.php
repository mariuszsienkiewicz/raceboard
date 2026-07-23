<?php

declare(strict_types=1);

namespace App\RaceCatalog\Application\Handler;

use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\RaceCatalog\Domain\Service\RaceRatingProviderInterface;
use App\Review\Domain\Event\ReviewAdded;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RecalculateRaceAverageOnReviewAddedHandler
{
    public function __construct(
        private RaceRatingProviderInterface $raceRatingProvider,
        private RaceRepositoryInterface $raceRepository,
    ) {
    }

    public function __invoke(ReviewAdded $event): void
    {
        $averageRating = $this->raceRatingProvider->getAverageRating($event->raceId);
        if (null === $averageRating) {
            // should not happen - anomaly, should be logged and investigated
            return;
        }

        $race = $this->raceRepository->findById($event->raceId);
        if (null === $race) {
            return;
        }

        $race->updateAverageRating($averageRating);
        $this->raceRepository->save($race);
    }
}
