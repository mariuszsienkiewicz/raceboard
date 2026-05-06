<?php

declare(strict_types=1);

namespace Tests\Unit\DataImport\Application;

use App\DataImport\Application\ImportRacesHandler;
use App\DataImport\Domain\RawRaceData;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Model\RaceId;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImportRacesHandlerTest extends TestCase
{
    private RaceRepositoryInterface&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RaceRepositoryInterface::class);
    }

    public function testImportsNewRaceWhenSlugDoesNotExist(): void
    {
        $this->repository->method('findBySlug')->willReturn(null);
        $this->repository->expects($this->once())->method('save');

        $handler = new ImportRacesHandler($this->repository);

        $result = $handler->handle([
            new RawRaceData(
                'Maraton Warszawski',
                '2026-09-27',
                'Warszawa',
                'mazowieckie',
                [],
                'https://example.com/race',
                null,
            ),
        ]);

        $this->assertSame(1, $result->importedCount);
        $this->assertSame(0, $result->skippedCount);
    }

    public function testSkipsRaceWhenSlugAlreadyExists(): void
    {
        $raceToBeFound = Race::create(
            RaceId::generate(),
            'Maraton Warszawski',
            'Warszawa',
            'mazowieckie',
        );

        $this->repository->method('findBySlug')->willReturn($raceToBeFound);
        $this->repository->expects($this->never())->method('save');

        $handler = new ImportRacesHandler($this->repository);

        $result = $handler->handle([
            new RawRaceData(
                'Maraton Warszawski',
                '2026-09-27',
                'Warszawa',
                'mazowieckie',
                [],
                'https://example.com/race',
                null,
            ),
        ]);

        $this->assertSame(0, $result->importedCount);
        $this->assertSame(1, $result->skippedCount);
    }

    public function testImportsMixOfNewAndExistingRaces(): void
    {
        $this->repository->method('findBySlug')->willReturnMap([
            ['maraton-warszawski', $this->createStub(Race::class)],
            ['maraton-krakowski', null],
            ['maraton-gdanski', $this->createStub(Race::class)],
        ]);

        $this->repository->expects($this->once())->method('save')->with($this->callback(function (Race $race) {
            return 'Maraton Krakowski' === $race->getName() && 'Kraków' === $race->getCity();
        }));

        $handler = new ImportRacesHandler($this->repository);

        $result = $handler->handle([
            new RawRaceData(
                'Maraton Warszawski',
                '2026-09-27',
                'Warszawa',
                'mazowieckie',
                [],
                'https://example.com/race1',
                null,
            ),
            new RawRaceData(
                'Maraton Krakowski',
                '2026-10-04',
                'Kraków',
                'małopolskie',
                [],
                'https://example.com/race2',
                null,
            ),
            new RawRaceData(
                'Maraton Gdański',
                '2026-10-11',
                'Gdańsk',
                'pomorskie',
                [],
                'https://example.com/race3',
                null,
            ),
        ]);

        $this->assertSame(1, $result->importedCount);
        $this->assertSame(2, $result->skippedCount);
    }

    public function testCreatesEditionWithCorrectDate(): void
    {
        $this->repository->method('findBySlug')->willReturn(null);
        $this->repository->expects($this->once())->method('save')->with($this->callback(function (Race $race) {
            $editions = $race->getEditions();

            return 1 === \count($editions) && '2026-11-15' === $editions[0]->getDate()->format('Y-m-d');
        }));

        $handler = new ImportRacesHandler($this->repository);
        $handler->handle([
            new RawRaceData(
                'Maraton Testowy',
                '2026-11-15',
                'Testowo',
                'testowe',
                [],
                'https://example.com/race',
                null,
            ),
        ]);
    }

    public function testSkipsEditionWhenDateIsInvalid(): void
    {
        $this->repository->method('findBySlug')->willReturn(null);
        $this->repository->expects($this->never())->method('save');

        $handler = new ImportRacesHandler($this->repository);
        $result = $handler->handle([
            new RawRaceData(
                'Maraton Testowy',
                'invalid-date',
                'Testowo',
                'testowe',
                [],
                'https://example.com/race',
                null,
            ),
        ]);

        $this->assertSame(0, $result->importedCount);
        $this->assertSame(1, $result->skippedCount);
    }

    public function testReturnsCorrectImportResultCounts(): void
    {
        $this->repository->method('findBySlug')->willReturnMap([
            ['maraton-warszawski', $this->createStub(Race::class)],
            ['maraton-krakowski', null],
            ['maraton-gdanski', $this->createStub(Race::class)],
            ['maraton-testowy', null],
            ['maraton-poznanski', $this->createStub(Race::class)],
        ]);

        $this->repository->expects($this->exactly(2))->method('save');

        $handler = new ImportRacesHandler($this->repository);
        $result = $handler->handle([
            new RawRaceData(
                'Maraton Warszawski',
                '2026-09-27',
                'Warszawa',
                'mazowieckie',
                [],
                'https://example.com/race1',
                null,
            ),
            new RawRaceData(
                'Maraton Krakowski',
                '2026-10-04',
                'Kraków',
                'małopolskie',
                [],
                'https://example.com/race2',
                null,
            ),
            new RawRaceData(
                'Maraton Gdański',
                '2026-10-11',
                'Gdańsk',
                'pomorskie',
                [],
                'https://example.com/race3',
                null,
            ),
            new RawRaceData(
                'Maraton Testowy',
                '2026-11-15',
                'Testowo',
                'testowe',
                [],
                'https://example.com/race4',
                null,
            ),
            new RawRaceData(
                'Maraton Poznański',
                '2026-10-18',
                'Poznań',
                'wielkopolskie',
                [],
                'https://example.com/race5',
                null,
            ),
        ]);

        $this->assertSame(2, $result->importedCount);
        $this->assertSame(3, $result->skippedCount);
    }

    public function testDoesNotSaveWhenAllRacesAlreadyExist(): void
    {
        $this->repository->method('findBySlug')->willReturn($this->createStub(Race::class));
        $this->repository->expects($this->never())->method('save');

        $handler = new ImportRacesHandler($this->repository);
        $result = $handler->handle([
            new RawRaceData(
                'Maraton Warszawski',
                '2026-09-27',
                'Warszawa',
                'mazowieckie',
                [],
                'https://example.com/race1',
                null,
            ),
            new RawRaceData(
                'Maraton Krakowski',
                '2026-10-04',
                'Kraków',
                'małopolskie',
                [],
                'https://example.com/race2',
                null,
            ),
            new RawRaceData(
                'Maraton Gdański',
                '2026-10-11',
                'Gdańsk',
                'pomorskie',
                [],
                'https://example.com/race3',
                null,
            ),
        ]);

        $this->assertSame(0, $result->importedCount);
        $this->assertSame(3, $result->skippedCount);
    }

    public function testHandlesEmptyInputList(): void
    {
        $this->repository->expects($this->never())->method('save');

        $handler = new ImportRacesHandler($this->repository);
        $result = $handler->handle([]);

        $this->assertSame(0, $result->importedCount);
        $this->assertSame(0, $result->skippedCount);
    }
}
