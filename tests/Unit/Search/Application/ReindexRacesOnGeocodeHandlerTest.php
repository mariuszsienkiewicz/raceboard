<?php

declare(strict_types=1);

namespace Tests\Unit\Search\Application;

use App\RaceCatalog\Domain\Event\RacesGeocoded;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Search\Application\Handler\ReindexRacesOnGeocodeHandler;
use App\Search\Domain\SearchIndexInterface;
use App\Shared\Domain\Model\RaceId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReindexRacesOnGeocodeHandlerTest extends TestCase
{
    private RaceRepositoryInterface&MockObject $repository;
    private SearchIndexInterface&MockObject $searchIndex;
    private ReindexRacesOnGeocodeHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RaceRepositoryInterface::class);
        $this->searchIndex = $this->createMock(SearchIndexInterface::class);
        $this->handler = new ReindexRacesOnGeocodeHandler($this->repository, $this->searchIndex);
    }

    public function testReindexesAllRacesWhenGeocodingSucceeded(): void
    {
        $race = Race::create(RaceId::generate(), 'Test Race', 'Warszawa', 'mazowieckie');
        $races = [$race];

        $this->repository->expects($this->once())->method('findAll')->willReturn($races);
        $this->searchIndex->expects($this->once())->method('indexAll')->with($races);

        ($this->handler)(new RacesGeocoded([$race->getId()]));
    }

    public function testSkipsReindexWhenNoRaceIds(): void
    {
        $this->repository->expects($this->never())->method('findAll');
        $this->searchIndex->expects($this->never())->method('indexAll');

        ($this->handler)(new RacesGeocoded([]));
    }
}
