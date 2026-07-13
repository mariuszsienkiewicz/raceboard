<?php

declare(strict_types=1);

namespace App\Tests\Unit\RaceCatalog\Domain\Model;

use App\RaceCatalog\Domain\Event\RaceCreated;
use App\RaceCatalog\Domain\Exception\DuplicateEditionException;
use App\RaceCatalog\Domain\Model\Distance;
use App\RaceCatalog\Domain\Model\Edition;
use App\RaceCatalog\Domain\Model\Race;
use App\Shared\Domain\Model\RaceId;
use PHPUnit\Framework\TestCase;

final class RaceTest extends TestCase
{
    public function testCreateRaceGeneratesSlug(): void
    {
        $race = Race::create(
            RaceId::generate(),
            'Maraton Warszawski',
            'Warszawa',
            'mazowieckie',
        );

        $this->assertSame('maraton-warszawski', $race->getSlug());
    }

    public function testCreateRaceRecordsRaceCreatedEvent(): void
    {
        $race = Race::create(
            RaceId::generate(),
            'PKO Białystok Półmaraton',
            'Białystok',
            'podlaskie',
        );

        $events = $race->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(RaceCreated::class, $events[0]);
    }

    public function testCanAddFutureEdition(): void
    {
        $race = $this->createRace();
        $futureDate = new \DateTimeImmutable('+3 months');

        $edition = new Edition($futureDate);
        $race->addEdition($edition);

        $this->assertCount(1, $race->getEditions());
    }

    public function testCannotAddTwoEditionsInSameYear(): void
    {
        $race = $this->createRace();
        $nextYear = new \DateTimeImmutable('+1 year January');

        $race->addEdition(new Edition($nextYear));

        $this->expectException(DuplicateEditionException::class);
        $race->addEdition(new Edition($nextYear->modify('+1 month')));
    }

    public function testGetUpcomingEditionsFiltersOutPastEditions(): void
    {
        $race = $this->createRace();

        $race->addEdition(new Edition(new \DateTimeImmutable('+2 months')));
        $race->addEdition(new Edition(new \DateTimeImmutable('+14 months')));

        $upcoming = $race->getUpcomingEditions();

        $this->assertCount(2, $upcoming);
    }

    public function testEditionCanHaveDistances(): void
    {
        $edition = new Edition(new \DateTimeImmutable('+3 months'));
        $edition->addDistance(new Distance('Maraton', 42.195, 150.0));
        $edition->addDistance(new Distance('Półmaraton', 21.0975, 100.0));

        $this->assertCount(2, $edition->getDistances());
    }

    public function testDistanceKnowsIfItsMarathon(): void
    {
        $marathon = new Distance('Maraton', 42.195);
        $half = new Distance('Półmaraton', 21.0975);
        $ten = new Distance('10km', 10.0);

        $this->assertTrue($marathon->isMarathon());
        $this->assertTrue($half->isHalfMarathon());
        $this->assertFalse($ten->isMarathon());
        $this->assertFalse($ten->isHalfMarathon());
    }

    public function testUpdateVoivodeship(): void
    {
        $race = Race::create(RaceId::generate(), 'Test Race', 'Warszawa', '');
        $race->updateVoivodeship('mazowieckie');
        $this->assertSame('mazowieckie', $race->getVoivodeship());
    }

    public function testUpdateVoivodeshipDoesNotOverwriteExistingValue(): void
    {
        $race = Race::create(RaceId::generate(), 'Test Race', 'Warszawa', 'mazowieckie');
        $race->updateVoivodeship('pomorskie');
        $this->assertSame('mazowieckie', $race->getVoivodeship());
    }

    public function testFindsEditionByDate(): void
    {
        $race = $this->createRace();
        $date = new \DateTimeImmutable('+2 days');
        $edition = new Edition($date);
        $race->addEdition($edition);

        $dateToFind = new \DateTimeImmutable('+3 days');
        $found = $race->findEditionByDate($dateToFind);
        $this->assertSame($edition, $found);
    }

    public function testFindEditionByDateReturnsNullWhenNoMatch(): void
    {
        $race = $this->createRace();
        $race->addEdition(new Edition(new \DateTimeImmutable('+2 months')));

        $found = $race->findEditionByDate(new \DateTimeImmutable('+5 months'));
        $this->assertNull($found);
    }

    public function testFindEditionByDateMatchesExactDate(): void
    {
        $race = $this->createRace();
        $date = new \DateTimeImmutable('+3 months');
        $edition = new Edition($date);
        $race->addEdition($edition);

        $found = $race->findEditionByDate($date);
        $this->assertSame($edition, $found);
    }

    private function createRace(): Race
    {
        return Race::create(
            RaceId::generate(),
            'Maraton Warszawski',
            'Warszawa',
            'mazowieckie',
        );
    }
}
