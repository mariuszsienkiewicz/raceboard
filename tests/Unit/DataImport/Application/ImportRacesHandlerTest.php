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

    public function testUpdatesRaceWhenSlugAlreadyExists(): void
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
        $this->assertSame(1, $result->updatedCount);
    }

    public function testImportsMixOfNewAndExistingRaces(): void
    {
        $this->repository->method('findBySlug')->willReturnMap([
            ['maraton-warszawski', Race::create(RaceId::generate(), 'Maraton Warszawski', 'Warszawa', 'mazowieckie')],
            ['maraton-krakowski', null],
            ['maraton-gdanski', Race::create(RaceId::generate(), 'Maraton Gdański', 'Gdańsk', 'pomorskie')],
        ]);

        $this->repository->expects($this->once())->method('save')->with($this->callback(function (Race $race) {
            return 'Maraton Krakowski' === $race->getName();
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
        $this->assertSame(2, $result->updatedCount);
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
            ['maraton-warszawski', Race::create(RaceId::generate(), 'Maraton Warszawski', 'Warszawa', 'mazowieckie')],
            ['maraton-krakowski', null],
            ['maraton-gdanski', Race::create(RaceId::generate(), 'Maraton Gdański', 'Gdańsk', 'pomorskie')],
            ['maraton-testowy', null],
            ['maraton-poznanski', Race::create(RaceId::generate(), 'Maraton Poznański', 'Poznań', 'wielkopolskie')],
        ]);
        $this->repository->expects($this->exactly(2))->method('save');
        $this->searchIndex->expects($this->exactly(2))->method('indexRace');

        $result = $this->handler->handle([
            $this->createRawRaceData(
                'Maraton Warszawski',
                (new \DateTimeImmutable('+3 months'))->format('Y-m-d'),
                'Warszawa',
                'mazowieckie',
            ),
            $this->createRawRaceData(
                'Maraton Krakowski',
                (new \DateTimeImmutable('+4 months'))->format('Y-m-d'),
                'Kraków',
                'małopolskie',
            ),
            $this->createRawRaceData(
                'Maraton Gdański',
                (new \DateTimeImmutable('+5 months'))->format('Y-m-d'),
                'Gdańsk',
                'pomorskie',
            ),
            $this->createRawRaceData(
                'Maraton Testowy',
                (new \DateTimeImmutable('+6 months'))->format('Y-m-d'),
                'Testowo',
                'testowe',
            ),
            $this->createRawRaceData(
                'Maraton Poznański',
                (new \DateTimeImmutable('+7 months'))->format('Y-m-d'),
                'Poznań',
                'wielkopolskie',
            ),
        ]);

        $this->assertSame(2, $result->importedCount);
        $this->assertSame(3, $result->updatedCount);
    }

    public function testUpdatesAllWhenAllRacesAlreadyExist(): void
    {
        $this->repository->method('findBySlug')->willReturnMap([
            ['maraton-warszawski', Race::create(RaceId::generate(), 'Maraton Warszawski', 'Warszawa', '')],
            ['maraton-krakowski', Race::create(RaceId::generate(), 'Maraton Krakowski', 'Kraków', '')],
            ['maraton-gdanski', Race::create(RaceId::generate(), 'Maraton Gdański', 'Gdańsk', '')],
        ]);
        $this->repository->expects($this->exactly(3))->method('save');
        $this->searchIndex->expects($this->exactly(3))->method('indexRace');

        $result = $this->handler->handle([
            $this->createRawRaceData(
                'Maraton Warszawski',
                (new \DateTimeImmutable('+3 months'))->format('Y-m-d'),
                'Warszawa',
                'mazowieckie',
            ),
            $this->createRawRaceData(
                'Maraton Krakowski',
                (new \DateTimeImmutable('+4 months'))->format('Y-m-d'),
                'Kraków',
                'małopolskie',
            ),
            $this->createRawRaceData(
                'Maraton Gdański',
                (new \DateTimeImmutable('+5 months'))->format('Y-m-d'),
                'Gdańsk',
                'pomorskie',
            ),
        ]);

        $this->assertSame(0, $result->importedCount);
        $this->assertSame(3, $result->updatedCount);
    }

    public function testHandlesEmptyInputList(): void
    {
        $this->repository->expects($this->never())->method('save');
        $this->searchIndex->expects($this->never())->method('indexRace');

        $result = $this->handler->handle([]);

        $this->assertSame(0, $result->importedCount);
        $this->assertSame(0, $result->skippedCount);
    }

    public function testEnrichesExistingRaceWithMissingVoivodeship(): void
    {
        $existingRace = Race::create(
            RaceId::generate(),
            'Test Marathon',
            'Test City',
            '',
        );

        $this->repository->expects($this->once())->method('findBySlug')->with('test-marathon')->willReturn($existingRace);
        $this->repository->expects($this->once())->method('save')->with($this->callback(
            fn (Race $race) => 'mazowieckie' === $race->getVoivodeship(),
        ));
        $this->searchIndex->expects($this->once())->method('indexRace');

        $result = $this->handler->handle([
            $this->createRawRaceData('Test Marathon', null, 'Test City', 'mazowieckie'),
        ]);

        $this->assertSame(0, $result->importedCount);
        $this->assertSame(1, $result->updatedCount);
    }

    public function testEnrichesExistingRaceWithMissingDistances(): void
    {
        $futureDate = new \DateTimeImmutable('+3 months');
        $existingRace = Race::create(RaceId::generate(), 'Test Race', 'City', 'mazowieckie');
        $existingRace->addEdition(new \App\RaceCatalog\Domain\Model\Edition($futureDate));

        $this->repository->method('findBySlug')->willReturn($existingRace);
        $this->repository->expects($this->once())->method('save');
        $this->searchIndex->expects($this->once())->method('indexRace');

        $result = $this->handler->handle([
            new RawRaceData(
                'Test Race',
                $futureDate->format('Y-m-d'),
                'City',
                'mazowieckie',
                [['name' => '10 km', 'lengthInKm' => 10.0, 'priceInPln' => null]],
                'https://example.com',
                null,
            ),
        ]);

        $this->assertSame(1, $result->updatedCount);
    }

    public function testDoesNotDuplicateExistingDistances(): void
    {
        $futureDate = new \DateTimeImmutable('+3 months');
        $existingRace = Race::create(RaceId::generate(), 'Test Race', 'City', 'mazowieckie');
        $edition = new \App\RaceCatalog\Domain\Model\Edition($futureDate);
        $edition->addDistance(new \App\RaceCatalog\Domain\Model\Distance('Maraton', 42.195, null));
        $existingRace->addEdition($edition);

        $this->repository->method('findBySlug')->willReturn($existingRace);
        $this->repository->expects($this->never())->method('save');
        $this->searchIndex->expects($this->never())->method('indexRace');

        $result = $this->handler->handle([
            new RawRaceData(
                'Test Race',
                $futureDate->format('Y-m-d'),
                'City',
                'mazowieckie',
                [['name' => 'Maraton', 'lengthInKm' => 42.195, 'priceInPln' => null]],
                'https://example.com',
                null,
            ),
        ]);

        $this->assertSame(1, $result->updatedCount);
    }

    /**
     * @param list<array{name: string, lengthInKm: float, priceInPln: float|null}> $distances
     */
    private function createRawRaceData(
        string $name = 'Maraton Testowy',
        ?string $date = null,
        string $city = 'Testowo',
        string $voivodeship = 'testowe',
        array $distances = [],
    ): RawRaceData {
        return new RawRaceData(
            $name,
            $date ?? (new \DateTimeImmutable('+3 months'))->format('Y-m-d'),
            $city,
            $voivodeship,
            $distances,
            'https://example.com/race',
            null,
        );
    }
}
