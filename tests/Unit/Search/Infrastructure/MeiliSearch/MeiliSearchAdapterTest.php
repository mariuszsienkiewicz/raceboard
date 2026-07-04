<?php

declare(strict_types=1);

namespace App\Tests\Unit\Search\Infrastructure\MeiliSearch;

use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Model\RaceId;
use App\Search\Domain\SearchIndexInterface;
use App\Search\Domain\SearchQuery;
use App\Search\Infrastructure\MeiliSearch\MeiliSearchAdapter;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class MeiliSearchAdapterTest extends TestCase
{
    private Client&MockObject $client;
    private Indexes&MockObject $index;
    private SearchIndexInterface $adapter;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->index = $this->createMock(Indexes::class);
        $this->client->method('index')->willReturn($this->index);
        $this->adapter = new MeiliSearchAdapter($this->client);
    }

    public function testIndexRacePassesCorrectDocumentToClient(): void
    {
        $race = Race::create(
            RaceId::generate(),
            'Test Race',
            'Test City',
            'Test Voivodeship',
        );

        $this->index->expects($this->once())
            ->method('addDocuments')
            ->with($this->callback(
                fn (array $documents) => 1 === \count($documents)
                && 'Test Race' === $documents[0]['name']
                && 'Test City' === $documents[0]['city']
                && $documents[0]['id'] === $race->getId()->toString(),
            ));

        $this->adapter->indexRace($race);
    }

    public function testIndexAllPassesAllDocumentsInSingleBatch(): void
    {
        $races = [
            Race::create(
                RaceId::generate(),
                'Race 1',
                'City 1',
                'Voivodeship 1',
            ),
            Race::create(
                RaceId::generate(),
                'Race 2',
                'City 2',
                'Voivodeship 2',
            ),
            Race::create(
                RaceId::generate(),
                'Race 3',
                'City 3',
                'Voivodeship 3',
            ),
        ];

        $this->index->expects($this->once())
            ->method('addDocuments')
            ->with($this->callback(
                fn (array $documents) => 3 === \count($documents)
                && 'Race 1' === $documents[0]['name']
                && 'Race 2' === $documents[1]['name']
                && 'Race 3' === $documents[2]['name'],
            ));

        $this->adapter->indexAll($races);
    }

    public function testSearchPassesFiltersToClient(): void
    {
        $query = new SearchQuery('maraton', 'Warszawa', 'mazowieckie', 42.195);

        $searchResults = $this->createStub(\Meilisearch\Search\SearchResult::class);
        $searchResults->method('getHits')->willReturn([]);
        $searchResults->method('getEstimatedTotalHits')->willReturn(0);

        $this->index->expects($this->once())
            ->method('search')
            ->with(
                'maraton',
                $this->callback(function (array $options) {
                    return isset($options['filter'])
                        && str_contains($options['filter'], 'city = "Warszawa"')
                        && str_contains($options['filter'], 'voivodeship = "mazowieckie"')
                        && str_contains($options['filter'], 'distances = 42.195');
                }),
            )
            ->willReturn($searchResults);

        $this->adapter->search($query);
    }

    public function testSearchReturnsCorrectSearchResult(): void
    {
        $query = new SearchQuery('maraton');

        $hits = [
            ['id' => 'abc-123', 'name' => 'Maraton Warszawski', 'city' => 'Warszawa'],
            ['id' => 'def-456', 'name' => 'Maraton Krakowski', 'city' => 'Kraków'],
        ];

        $searchResults = $this->createStub(\Meilisearch\Search\SearchResult::class);
        $searchResults->method('getHits')->willReturn($hits);
        $searchResults->method('getTotalHits')->willReturn(2);

        $this->index->expects($this->once())->method('search')->willReturn($searchResults);

        $result = $this->adapter->search($query);

        $this->assertSame(2, $result->totalHits);
        $this->assertCount(2, $result->hits);
        $this->assertSame('Maraton Warszawski', $result->hits[0]['name']);
        $this->assertSame('Maraton Krakowski', $result->hits[1]['name']);
        $this->assertSame(1, $result->page);
        $this->assertSame(20, $result->perPage);
    }

    public function testSearchWithNoFiltersOmitsFilterOption(): void
    {
        $query = new SearchQuery('maraton');

        $searchResults = $this->createStub(\Meilisearch\Search\SearchResult::class);
        $searchResults->method('getHits')->willReturn([]);
        $searchResults->method('getEstimatedTotalHits')->willReturn(0);

        $this->index->expects($this->once())
            ->method('search')
            ->with(
                'maraton',
                $this->callback(function (array $options) {
                    return !isset($options['filter']);
                }),
            )
            ->willReturn($searchResults);

        $this->adapter->search($query);
    }

    public function testSearchCalculatesPageAndHitsPerPage(): void
    {
        $query = new SearchQuery('maraton', 'Warszawa', 'mazowieckie', 42.195, null, null, 2, 40);

        $searchResults = $this->createStub(\Meilisearch\Search\SearchResult::class);
        $searchResults->method('getHits')->willReturn([]);
        $searchResults->method('getTotalHits')->willReturn(0);

        $this->index->expects($this->once())
            ->method('search')
            ->with(
                'maraton',
                $this->callback(function (array $options) {
                    return isset($options['page']) && 2 === $options['page'] && isset($options['hitsPerPage']) && 40 === $options['hitsPerPage'];
                }),
            )
            ->willReturn($searchResults);

        $this->adapter->search($query);
    }
}
