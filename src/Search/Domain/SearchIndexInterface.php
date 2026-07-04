<?php

declare(strict_types=1);

namespace App\Search\Domain;

use App\RaceCatalog\Domain\Model\Race;

interface SearchIndexInterface
{
    public function configureIndex(): void;

    public function indexRace(Race $race): void;

    /** @param list<Race> $races */
    public function indexAll(array $races): void;

    public function search(SearchQuery $query): SearchResult;

    /** @return array<int, array<string, mixed>> */
    public function searchMapPoints(SearchQuery $query): array;
}
