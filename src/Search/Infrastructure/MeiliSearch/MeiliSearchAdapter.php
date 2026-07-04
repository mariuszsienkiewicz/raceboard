<?php

declare(strict_types=1);

namespace App\Search\Infrastructure\MeiliSearch;

use App\RaceCatalog\Domain\Model\Edition;
use App\RaceCatalog\Domain\Model\Race;
use App\Search\Domain\SearchIndexInterface;
use App\Search\Domain\SearchQuery;
use App\Search\Domain\SearchResult;
use App\Shared\Domain\CityCoordinates;
use Meilisearch\Client;

class MeiliSearchAdapter implements SearchIndexInterface
{
    protected const string INDEX_NAME = 'races';
    protected const int MAX_TOTAL_HITS = 5000;
    protected const int MAX_MAP_POINTS = 1000;

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function configureIndex(): void
    {
        $index = $this->client->index(self::INDEX_NAME);
        $index->updateFilterableAttributes(['city', 'voivodeship', 'dates', 'distances', '_geo']);
        $index->updateSortableAttributes(['dates']);
        $index->updateSearchableAttributes(['name', 'city', 'voivodeship']);
        $index->updatePagination(['maxTotalHits' => self::MAX_TOTAL_HITS]);
    }

    public function indexRace(Race $race): void
    {
        $document = $this->toDocument($race);
        $this->client->index(self::INDEX_NAME)->addDocuments([$document]);
    }

    /**
     * @param list<Race> $races
     */
    public function indexAll(array $races): void
    {
        $documents = array_map(fn (Race $race) => $this->toDocument($race), $races);
        $this->client->index(self::INDEX_NAME)->deleteAllDocuments();
        $this->client->index(self::INDEX_NAME)->addDocuments($documents);
    }

    public function search(SearchQuery $query): SearchResult
    {
        $filters = $this->buildFilters($query);

        $options = [
            'page' => $query->page,
            'hitsPerPage' => $query->perPage,
        ];

        if ('' !== $filters) {
            $options['filter'] = $filters;
        }

        $searchResult = $this->client->index(self::INDEX_NAME)->search($query->query, $options);

        return new SearchResult(
            $searchResult->getHits(),
            $searchResult->getTotalHits(),
            $query->page,
            $query->perPage,
            $searchResult->getTotalPages() ?? 1,
        );
    }

    public function searchMapPoints(SearchQuery $query): array
    {
        $filters = $this->buildFilters($query);

        $options = [
            'limit' => self::MAX_MAP_POINTS,
            'attributesToRetrieve' => ['id', 'name', 'city', '_geo'],
        ];

        if ('' !== $filters) {
            $options['filter'] = $filters;
        }

        $searchResult = $this->client->index(self::INDEX_NAME)->search($query->query, $options);

        return $searchResult->getHits();
    }

    private function buildFilters(SearchQuery $query): string
    {
        $filters = [];

        if (null !== $query->city) {
            $filters[] = \sprintf('city = "%s"', $query->city);
        }

        if (null !== $query->voivodeship) {
            $filters[] = \sprintf('voivodeship = "%s"', $query->voivodeship);
        }

        if (null !== $query->distanceKm) {
            $filters[] = \sprintf('distances = %s', $query->distanceKm);
        }

        if (null !== $query->dateFrom) {
            $from = (new \DateTimeImmutable($query->dateFrom))->getTimestamp();
            $filters[] = \sprintf('dates >= %d', $from);
        }

        if (null !== $query->dateTo) {
            $to = (new \DateTimeImmutable($query->dateTo))->getTimestamp();
            $filters[] = \sprintf('dates <= %d', $to);
        }

        if (null !== $query->topLat && null !== $query->bottomLat) {
            $filters[] = sprintf(
                '_geoBoundingBox([%f, %f], [%f, %f])',
                $query->topLat, $query->topLng,
                $query->bottomLat, $query->bottomLng,
            );
        }

        return implode(' AND ', $filters);
    }

    /**
     * @return array{
     *   city: string,
     *   dates: int[],
     *   distances: array<int, float>,
     *   id: string,
     *   name: string,
     *   slug: string,
     *   voivodeship: string
     * }
     */
    private function toDocument(Race $race): array
    {
        $coords = CityCoordinates::get($race->getCity());

        return [
            'id' => $race->getId()->toString(),
            'slug' => $race->getSlug(),
            'name' => $race->getName(),
            'city' => $race->getCity(),
            'voivodeship' => $race->getVoivodeship(),
            'dates' => array_map(fn (Edition $edition) => $edition->getDate()->getTimestamp(), $race->getEditions()),
            'distances' => $this->flattenDistances($race),
            '_geo' => $coords ? ['lat' => $coords['lat'], 'lng' => $coords['lng']] : null,
        ];
    }

    /** @return list<float> */
    private function flattenDistances(Race $race): array
    {
        $distances = [];
        foreach ($race->getEditions() as $edition) {
            foreach ($edition->getDistances() as $distance) {
                $distances[] = $distance->getLengthInKm();
            }
        }

        return array_values(array_unique($distances));
    }
}
