<?php

declare(strict_types=1);

namespace App\Tests\Integration\RaceCatalog\Infrastructure\Persistence;

use App\RaceCatalog\Domain\Model\RaceId;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineRaceRepositoryTest extends KernelTestCase
{
    public function testSavingAndRetrievingRace(): void
    {
        self::bootKernel();

        /** @var RaceRepositoryInterface */
        $raceRepository = self::getContainer()->get(RaceRepositoryInterface::class);

        // Create a new race
        $race = Race::create(
            RaceId::generate(),
            'Test Race',
            'Warsaw',
            'Masovian'
        );

        // Save the race
        $raceRepository->save($race);

        // Retrieve the race
        $retrievedRace = $raceRepository->findById($race->getId());

        // test that the retrieved race matches the original
        $this->assertNotNull($retrievedRace);
        $this->assertSame($race->getId(), $retrievedRace->getId());
        $this->assertSame($race->getName(), $retrievedRace->getName());
        $this->assertSame($race->getCity(), $retrievedRace->getCity());
        $this->assertSame($race->getVoivodeship(), $retrievedRace->getVoivodeship());
    }
}