<?php

declare(strict_types=1);

namespace App\Tests\Integration\RaceCatalog\Infrastructure\Persistence;

use App\RaceCatalog\Domain\Model\Distance;
use App\RaceCatalog\Domain\Model\Edition;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Model\RaceId;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
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
}
