<?php

declare(strict_types=1);

namespace App\Tests\Integration\RaceCatalog\Infrastructure\Persistence;

use App\RaceCatalog\Domain\Model\Distance;
use App\RaceCatalog\Domain\Model\Edition;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineRaceRepositoryTest extends KernelTestCase
{
    private RaceRepositoryInterface $repository;
    private \Doctrine\ORM\EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $repository = self::getContainer()->get(RaceRepositoryInterface::class);
        assert($repository instanceof RaceRepositoryInterface);
        $this->repository = $repository;

        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        assert($em instanceof \Doctrine\ORM\EntityManagerInterface);
        $this->em = $em;
    }

    public function testSavingAndRetrievingRace(): void
    {
        // Create a new race
        $race = Race::create(
            RaceId::generate(),
            'Test Race',
            'Warsaw',
            'Masovian',
        );

        // Save the race
        $this->repository->save($race);

        // Clear the EntityManager to ensure we fetch a fresh instance from the database
        $this->em->clear();

        // Retrieve the race
        $retrievedRace = $this->repository->findById($race->getId());

        // Test that the retrieved race matches the original
        $this->assertNotNull($retrievedRace);
        $this->assertEquals($race->getId(), $retrievedRace->getId());
        $this->assertEquals($race->getName(), $retrievedRace->getName());
        $this->assertEquals($race->getCity(), $retrievedRace->getCity());
        $this->assertEquals($race->getVoivodeship(), $retrievedRace->getVoivodeship());
    }

    public function testSavingRaceWithEditionsAndDistances(): void
    {
        // Create a new race
        $race = Race::create(
            RaceId::generate(),
            'Test Race',
            'Warsaw',
            'Masovian',
        );

        // Create an edition with distances
        $edition = new Edition(new \DateTimeImmutable('+3 months'));
        $edition->addDistance(new Distance('Maraton', 42.195, 150.0));
        $edition->addDistance(new Distance('Half-Marathon', 21.0975, 100.0));

        // Assign the edition to the race
        $race->addEdition($edition);

        // Save the race
        $this->repository->save($race);

        // Clear the EntityManager to ensure we fetch a fresh instance from the database
        $this->em->clear();

        // Retrieve the race
        $retrievedRace = $this->repository->findById($race->getId());

        // Test that the retrieved race has the edition and distances
        $this->assertNotNull($retrievedRace);
        $this->assertCount(1, $retrievedRace->getEditions());
        $retrievedEdition = $retrievedRace->getEditions()[0];
        $this->assertCount(2, $retrievedEdition->getDistances());
        $distances = $retrievedEdition->getDistances();
        $this->assertSame('Maraton', $distances[0]->getName());
        $this->assertSame(42.195, $distances[0]->getLengthInKm());
        $this->assertSame(150.0, $distances[0]->getPriceInPln());
        $this->assertSame('Half-Marathon', $distances[1]->getName());
        $this->assertSame(21.0975, $distances[1]->getLengthInKm());
        $this->assertSame(100.0, $distances[1]->getPriceInPln());
    }

    public function testFindByIdsReturnsCorrectRaces(): void
    {
        // Create multiple races
        $race1Id = RaceId::generate();
        $race2Id = RaceId::generate();
        $race3Id = RaceId::generate();

        $race1 = Race::create($race1Id, 'Test Race 1', 'Warsaw', 'Masovian');
        $race2 = Race::create($race2Id, 'Test Race 2', 'Warsaw', 'Masovian');
        $race3 = Race::create($race3Id, 'Test Race 3', 'Warsaw', 'Masovian');

        $this->repository->save($race1);
        $this->repository->save($race2);
        $this->repository->save($race3);

        // Clear the EntityManager to ensure we fetch fresh instances from the database
        $this->em->clear();

        // Retrieve races by IDs
        $retrievedRaces = $this->repository->findByIds([$race1Id, $race3Id]);

        // Test that the correct races are retrieved
        $this->assertCount(2, $retrievedRaces);
        $this->assertArrayHasKey($race1Id->toString(), $retrievedRaces);
        $this->assertArrayHasKey($race3Id->toString(), $retrievedRaces);
        $this->assertEquals($race1->getName(), $retrievedRaces[$race1Id->toString()]->getName());
        $this->assertEquals($race3->getName(), $retrievedRaces[$race3Id->toString()]->getName());
    }

    public function testFindByIdsReturnsEmptyArrayForEmptyInput(): void
    {
        $result = $this->repository->findByIds([]);
        $this->assertSame([], $result);
    }

    public function testFindBySlug(): void
    {
        // Create a new race
        $race = Race::create(
            RaceId::generate(),
            'Test Race',
            'Warsaw',
            'Masovian',
        );

        // Save the race
        $this->repository->save($race);

        // Clear the EntityManager to ensure we fetch a fresh instance from the database
        $this->em->clear();

        // Retrieve the race by slug
        $retrievedRace = $this->repository->findBySlug($race->getSlug());

        // Test that the retrieved race matches the original
        $this->assertNotNull($retrievedRace);
        $this->assertEquals($race->getId(), $retrievedRace->getId());
        $this->assertEquals($race->getName(), $retrievedRace->getName());
        $this->assertEquals($race->getCity(), $retrievedRace->getCity());
        $this->assertEquals($race->getVoivodeship(), $retrievedRace->getVoivodeship());
    }

    public function testFindAllReturnsAllRaces(): void
    {
        // Create and save multiple races
        $race1 = Race::create(
            RaceId::generate(),
            'Test Race 1',
            'Warsaw',
            'Masovian',
        );
        $race2 = Race::create(
            RaceId::generate(),
            'Test Race 2',
            'Krakow',
            'Lesser Poland',
        );

        $this->repository->save($race1);
        $this->repository->save($race2);

        // Clear the EntityManager to ensure we fetch fresh instances from the database
        $this->em->clear();

        // Retrieve all races
        $allRaces = $this->repository->findAll();

        // Test that both races are retrieved
        $this->assertCount(2, $allRaces);
    }

    public function testFindByIdReturnsNullForNonExistentRace(): void
    {
        // Attempt to retrieve a race with a non-existent ID
        $nonExistentRaceId = RaceId::generate();
        $retrievedRace = $this->repository->findById($nonExistentRaceId);

        // Test that the retrieved race is null
        $this->assertNull($retrievedRace);
    }

    public function testFindSimilarReturnsRacesWithSameDateAndCity(): void
    {
        // Create a new race
        $race = Race::create(
            RaceId::generate(),
            'Test Race',
            'Warsaw',
            'Masovian',
        );

        $futureDate = new \DateTimeImmutable('+3 months');
        $searchDate = $futureDate->modify('-1 day')->format('Y-m-d');

        $edition = new Edition($futureDate);
        $race->addEdition($edition);

        $this->repository->save($race);
        $this->em->clear();

        $similarRaces = $this->repository->findSimilar($searchDate, 'Warsaw');

        $this->assertCount(1, $similarRaces);
    }

    public function testCountReturnsZeroWhenNoRaces(): void
    {
        $this->addRacesToDatabase(0);
        $count = $this->repository->count();
        $this->assertSame(0, $count);
    }

    public function testCountReturnsTotalNumberOfRaces(): void
    {
        $this->addRacesToDatabase(20);
        $count = $this->repository->count();
        $this->assertSame(20, $count);
    }

    public function testCountIgnoresPaginationLimit(): void
    {
        $this->addRacesToDatabase(100);
        $count = $this->repository->count();
        $this->assertSame(100, $count);
    }

    public function testReturnsFullPageWhenEnoughRaces(): void
    {
        $this->addRacesToDatabase(15);
        $result = $this->repository->findPaginatedWithDetails(10, 0);
        $result = iterator_to_array($result);
        $this->assertCount(10, $result);
    }

    public function testReturnsRemainderOnLastPage(): void
    {
        $this->addRacesToDatabase(15);

        $result = $this->repository->findPaginatedWithDetails(10, 0);
        $result = iterator_to_array($result);

        $this->assertCount(10, $result);

        $result = $this->repository->findPaginatedWithDetails(10, 10);
        $result = iterator_to_array($result);

        $this->assertCount(5, $result);
    }

    public function testReturnsEmptyWhenOffsetBeyondTotal(): void
    {
        $this->addRacesToDatabase(15);
        $result = $this->repository->findPaginatedWithDetails(10, 999);
        $result = iterator_to_array($result);
        $this->assertCount(0, $result);
    }

    public function testDoesNotReturnDuplicateRacesAcrossPages(): void
    {
        $this->addRacesToDatabase(15);

        $page1 = iterator_to_array($this->repository->findPaginatedWithDetails(10, 0));
        $page2 = iterator_to_array($this->repository->findPaginatedWithDetails(10, 10));

        $page1Ids = array_map(fn (Race $race) => $race->getId()->toString(), $page1);
        $page2Ids = array_map(fn (Race $race) => $race->getId()->toString(), $page2);

        $this->assertSame([], array_intersect($page1Ids, $page2Ids));
    }

    public function testLimitCountsRacesNotJoinedRows(): void
    {
        $this->addRaceWithEditionsAndDistances();
        $result = $this->repository->findPaginatedWithDetails(1, 0);
        $result = iterator_to_array($result);
        $this->assertCount(1, $result);
    }

    public function testReturnsRacesWithEditionsAndDistancesLoaded(): void
    {
        $this->addRaceWithEditionsAndDistances();
        $this->em->clear(); // detach entities

        $races = iterator_to_array($this->repository->findPaginatedWithDetails(10, 0));
        $race = $races[0];

        $editions = $race->getEditions();
        $this->assertCount(3, $editions);
        $this->assertCount(2, $editions[0]->getDistances());
    }

    private function addRaceWithEditionsAndDistances(): void
    {
        $race = Race::create(
            RaceId::generate(),
            'Test Race',
            'Warsaw',
            'Masovian',
        );
        $edition = new Edition(new \DateTimeImmutable('+3 months'));
        $edition->addDistance(new Distance('5 km', 5.0, 50.0));
        $edition->addDistance(new Distance('10 km', 10.0, 80.0));

        $edition2 = new Edition(new \DateTimeImmutable('+6 months'));
        $edition2->addDistance(new Distance('10 km', 10.0, 50.0));
        $edition2->addDistance(new Distance('15 km', 15.0, 100.0));

        $edition3 = new Edition(new \DateTimeImmutable('+18 months'));
        $edition3->addDistance(new Distance('21 km', 5.0, 50.0));
        $edition3->addDistance(new Distance('42 km', 10.0, 80.0));

        $race->addEdition($edition);
        $race->addEdition($edition2);
        $race->addEdition($edition3);

        $this->repository->save($race);
        $this->em->clear();
    }

    private function addRacesToDatabase(int $count = 10): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $race = Race::create(
                RaceId::generate(),
                'Test Race '.$i,
                'Warsaw',
                'Masovian',
            );

            $upcomingEdition = new Edition(new \DateTimeImmutable('+3 months'));
            $upcomingEdition->addDistance(new Distance('5 km', 5.0, 50.0));
            $upcomingEdition->addDistance(new Distance('10 km', 10.0, 80.0));

            $nextYearEdition = new Edition(new \DateTimeImmutable('+15 months'));
            $nextYearEdition->addDistance(new Distance('Półmaraton', 21.0975, 100.0));

            $race->addEdition($upcomingEdition);
            $race->addEdition($nextYearEdition);

            $this->repository->save($race);
        }

        $this->em->clear();
    }
}
