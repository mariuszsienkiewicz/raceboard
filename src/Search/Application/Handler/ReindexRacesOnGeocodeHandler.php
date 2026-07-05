<?php

declare(strict_types=1);

namespace App\Search\Application\Handler;

use App\RaceCatalog\Domain\Event\RacesGeocoded;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Search\Domain\SearchIndexInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ReindexRacesOnGeocodeHandler
{
    public function __construct(
        private RaceRepositoryInterface $raceRepository,
        private SearchIndexInterface $searchIndex,
    ) {
    }

    public function __invoke(RacesGeocoded $event): void
    {
        if ([] === $event->raceIds) {
            return;
        }

        $this->searchIndex->indexAll($this->raceRepository->findAll());
    }
}
