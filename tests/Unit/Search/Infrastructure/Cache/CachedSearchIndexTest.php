<?php

declare(strict_types=1);

namespace App\Tests\Unit\Search\Infrastructure\Cache;

use App\RaceCatalog\Domain\Model\Race;
use App\Search\Domain\SearchIndexInterface;
use App\Search\Domain\SearchQuery;
use App\Search\Domain\SearchResult;
use App\Search\Infrastructure\Cache\CachedSearchIndex;
use App\Shared\Domain\Model\RaceId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CachedSearchIndexTest extends TestCase
{
    private SearchIndexInterface&MockObject $inner;
    private TagAwareAdapter $cache;
    private CachedSearchIndex $sut;

    public function setUp(): void
    {
        $this->inner = $this->createMock(SearchIndexInterface::class);
        $this->cache = new TagAwareAdapter(new ArrayAdapter());
        $this->sut = new CachedSearchIndex(
            $this->inner,
            $this->cache,
            new NullLogger(),
            300,
        );
    }

    public function testSearchCachesResultOnSecondCall(): void
    {
        $this->inner->expects($this->once())->method('search')->willReturn($this->searchResultMock());
        $this->sut->search(new SearchQuery('test'));
        $this->sut->search(new SearchQuery('test'));
    }

    public function testDifferentQueriesHitInnerTwice(): void
    {
        $this->inner->expects($this->exactly(2))->method('search')->willReturn($this->searchResultMock());
        $this->sut->search(new SearchQuery('test'));
        $this->sut->search(new SearchQuery('test2'));
    }

    public function testIndexRaceInvalidatesCache(): void
    {
        $this->inner->expects($this->exactly(2))->method('search')->willReturn($this->searchResultMock());
        $this->sut->search(new SearchQuery('test'));

        $race = Race::create(RaceId::generate(), 'test', 'test', 'test');
        $this->inner->expects($this->once())->method('indexRace')->with($race);
        $this->sut->indexRace($race);

        $this->sut->search(new SearchQuery('test'));
    }

    public function testSearchFallsBackWhenCacheFails(): void
    {
        $cacheMock = $this->createMock(TagAwareCacheInterface::class);
        $cacheMock->expects($this->once())
            ->method('get')
            ->willThrowException(new \RuntimeException('Cache failed'));

        $expectedResult = $this->searchResultMock();

        $this->inner->expects($this->once())
            ->method('search')
            ->willReturn($expectedResult);

        $sut = new CachedSearchIndex(
            $this->inner,
            $cacheMock,
            new NullLogger(),
            300,
        );

        $result = $sut->search(new SearchQuery('test'));

        self::assertSame($expectedResult, $result);
    }

    private function searchResultMock(): SearchResult
    {
        return new SearchResult(
            hits: [['id' => '1', 'name' => 'Test']],
            totalHits: 1,
            page: 1,
            perPage: 20,
            totalPages: 1,
        );
    }
}
