<?php

declare(strict_types=1);

namespace Tests\Unit\DataImport\Application;

use App\DataImport\Application\DuplicateDetector;
use App\DataImport\Application\ImportRacesHandler;
use App\DataImport\Domain\RawRaceData;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Model\RaceId;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Search\Domain\SearchIndexInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImportRacesHandlerTest extends TestCase
{
    private RaceRepositoryInterface&MockObject $repository;
    private DuplicateDetector $duplicateDetector;
    private SearchIndexInterface&MockObject $searchIndex;
    private ImportRacesHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RaceRepositoryInterface::class);
        $this->repository->method('findSimilar')->willReturn([]);
        $this->duplicateDetector = new DuplicateDetector();
        $this->searchIndex = $this->createMock(SearchIndexInterface::class);
        $this->handler = new ImportRacesHandler($this->repository, $this->duplicateDetector, $this->searchIndex);
    }

    public function testImportsNewRaceWhenSlugDoesNotExist(): void
    {
        $this->repository->method('findBySlug')->willReturn(null);
        $this->repository->expects($this->once())->method('save');
        $this->searchIndex->expects($this->once())->method('indexRace');

        $futureDate = (new \DateTimeImmutable('+3 months'))->format('Y-m-d');

        $result = $this->handler->handle([
            $this->createRawRaceData(
                'Maraton Warszawski',
                $futureDate,
                'Warszawa',
                'mazowieckie',
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
        $this->searchIndex->expects($this->never())->method('indexRace');

        $futureDate = (new \DateTimeImmutable('+3 months'))->format('Y-m-d');

        $result = $this->handler->handle([
            $this->createRawRaceData(
                'Maraton Warszawski',
                $futureDate,
                'Warszawa',
                'mazowieckie',
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
        $this->searchIndex->expects($this->once())->method('indexRace');

        $futureDate1 = (new \DateTimeImmutable('+3 months'))->format('Y-m-d');
        $futureDate2 = (new \DateTimeImmutable('+4 months'))->format('Y-m-d');
        $futureDate3 = (new \DateTimeImmutable('+5 months'))->format('Y-m-d');

        $result = $this->handler->handle([
            $this->createRawRaceData(
                'Maraton Warszawski',
                $futureDate1,
                'Warszawa',
                'mazowieckie',
            ),
            $this->createRawRaceData(
                'Maraton Krakowski',
                $futureDate2,
                'Kraków',
                'małopolskie',
            ),
            $this->createRawRaceData(
                'Maraton Gdański',
                $futureDate3,
                'Gdańsk',
                'pomorskie',
            ),
        ]);

        $this->assertSame(1, $result->importedCount);
        $this->assertSame(2, $result->skippedCount);
    }

    public function testCreatesEditionWithCorrectDate(): void
    {
        $futureDate = (new \DateTimeImmutable('+3 months'))->format('Y-m-d');
        $this->repository->method('findBySlug')->willReturn(null);
        $this->repository->expects($this->once())->method('save')->with($this->callback(function (Race $race) use ($futureDate) {
            $editions = $race->getEditions();

            return 1 === \count($editions) && $futureDate === $editions[0]->getDate()->format('Y-m-d');
        }));
        $this->searchIndex->expects($this->once())->method('indexRace');

        $this->handler->handle([
            $this->createRawRaceData(
                'Maraton Testowy',
                $futureDate,
                'Testowo',
                'testowe',
            ),
        ]);
    }

    public function testSkipsEditionWhenDateIsInvalid(): void
    {
        $this->repository->method('findBySlug')->willReturn(null);
        $this->repository->expects($this->never())->method('save');
        $this->searchIndex->expects($this->never())->method('indexRace');

        $result = $this->handler->handle([
            $this->createRawRaceData(
                'Maraton Testowy',
                'invalid-date',
                'Testowo',
                'testowe',
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
        $this->searchIndex->expects($this->exactly(2))->method('indexRace');

        $futureDate1 = (new \DateTimeImmutable('+3 months'))->format('Y-m-d');
        $futureDate2 = (new \DateTimeImmutable('+4 months'))->format('Y-m-d');
        $futureDate3 = (new \DateTimeImmutable('+5 months'))->format('Y-m-d');
        $futureDate4 = (new \DateTimeImmutable('+6 months'))->format('Y-m-d');
        $futureDate5 = (new \DateTimeImmutable('+7 months'))->format('Y-m-d');
        $result = $this->handler->handle([
            $this->createRawRaceData(
                'Maraton Warszawski',
                $futureDate1,
                'Warszawa',
                'mazowieckie',
            ),
            $this->createRawRaceData(
                'Maraton Krakowski',
                $futureDate2,
                'Kraków',
                'małopolskie',
            ),
            $this->createRawRaceData(
                'Maraton Gdański',
                $futureDate3,
                'Gdańsk',
                'pomorskie',
            ),
            $this->createRawRaceData(
                'Maraton Testowy',
                $futureDate4,
                'Testowo',
                'testowe',
            ),
            $this->createRawRaceData(
                'Maraton Poznański',
                $futureDate5,
                'Poznań',
                'wielkopolskie',
            ),
        ]);

        $this->assertSame(2, $result->importedCount);
        $this->assertSame(3, $result->skippedCount);
    }

    public function testDoesNotSaveWhenAllRacesAlreadyExist(): void
    {
        $this->repository->method('findBySlug')->willReturn($this->createStub(Race::class));
        $this->repository->expects($this->never())->method('save');
        $this->searchIndex->expects($this->never())->method('indexRace');

        $futureDate1 = (new \DateTimeImmutable('+3 months'))->format('Y-m-d');
        $futureDate2 = (new \DateTimeImmutable('+4 months'))->format('Y-m-d');
        $futureDate3 = (new \DateTimeImmutable('+5 months'))->format('Y-m-d');
        $result = $this->handler->handle([
            $this->createRawRaceData(
                'Maraton Warszawski',
                $futureDate1,
                'Warszawa',
                'mazowieckie',
            ),
            $this->createRawRaceData(
                'Maraton Krakowski',
                $futureDate2,
                'Kraków',
                'małopolskie',
            ),
            $this->createRawRaceData(
                'Maraton Gdański',
                $futureDate3,
                'Gdańsk',
                'pomorskie',
            ),
        ]);

        $this->assertSame(0, $result->importedCount);
        $this->assertSame(3, $result->skippedCount);
    }

    public function testHandlesEmptyInputList(): void
    {
        $this->repository->expects($this->never())->method('save');
        $this->searchIndex->expects($this->never())->method('indexRace');

        $result = $this->handler->handle([]);

        $this->assertSame(0, $result->importedCount);
        $this->assertSame(0, $result->skippedCount);
    }

    private function createRawRaceData(
        string $name = 'Maraton Testowy',
        ?string $date = null,
        string $city = 'Testowo',
        string $voivodeship = 'testowe',
    ): RawRaceData {
        return new RawRaceData(
            $name,
            $date ?? (new \DateTimeImmutable('+3 months'))->format('Y-m-d'),
            $city,
            $voivodeship,
            [],
            'https://example.com/race',
            null,
        );
    }
}
