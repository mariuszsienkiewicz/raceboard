<?php

declare(strict_types=1);

namespace App\Tests\Unit\RaceCatalog\Domain\Model;

use App\RaceCatalog\Domain\Model\Distance;
use App\RaceCatalog\Domain\Model\Edition;
use PHPUnit\Framework\TestCase;

final class EditionTest extends TestCase
{
    public function testEditionHasDateSetOnConstruction(): void
    {
        $date = new \DateTimeImmutable('+3 months');
        $edition = new Edition($date);

        $this->assertSame($date, $edition->getDate());
    }

    public function testEditionHasNoRegistrationUrlByDefault(): void
    {
        $edition = new Edition(new \DateTimeImmutable('+3 months'));

        $this->assertNull($edition->getRegistrationUrl());
    }

    public function testEditionCanHaveRegistrationUrl(): void
    {
        $url = 'https://example.com/register';
        $edition = new Edition(new \DateTimeImmutable('+3 months'), $url);

        $this->assertSame($url, $edition->getRegistrationUrl());
    }

    public function testEditionStartsWithNoDistances(): void
    {
        $edition = new Edition(new \DateTimeImmutable('+3 months'));

        $this->assertCount(0, $edition->getDistances());
    }

    public function testCanAddDistanceToEdition(): void
    {
        $edition = new Edition(new \DateTimeImmutable('+3 months'));
        $edition->addDistance(new Distance('Maraton', 42.195, 150.0));

        $this->assertCount(1, $edition->getDistances());
    }

    public function testCanAddMultipleDistancesToEdition(): void
    {
        $edition = new Edition(new \DateTimeImmutable('+3 months'));
        $edition->addDistance(new Distance('Maraton', 42.195, 150.0));
        $edition->addDistance(new Distance('Półmaraton', 21.0975, 100.0));
        $edition->addDistance(new Distance('10km', 10.0, 60.0));

        $this->assertCount(3, $edition->getDistances());
    }

    public function testDistancesPassedToConstructorAreAdded(): void
    {
        $distances = [
            new Distance('Maraton', 42.195, 150.0),
            new Distance('Półmaraton', 21.0975, 100.0),
        ];

        $edition = new Edition(new \DateTimeImmutable('+3 months'), null, $distances);

        $this->assertCount(2, $edition->getDistances());
    }

    public function testAddDistanceAssignsEditionToDistance(): void
    {
        $edition = new Edition(new \DateTimeImmutable('+3 months'));
        $distance = new Distance('Maraton', 42.195);
        $edition->addDistance($distance);

        $distances = $edition->getDistances();

        $this->assertSame($distance, $distances[0]);

        $reflection = new \ReflectionProperty($distance, 'edition');

        $this->assertSame($edition, $reflection->getValue($distance));
    }

    public function testGetDistancesReturnsAllAddedDistances(): void
    {
        $marathon = new Distance('Maraton', 42.195, 150.0);
        $half = new Distance('Półmaraton', 21.0975, 100.0);

        $edition = new Edition(new \DateTimeImmutable('+3 months'));
        $edition->addDistance($marathon);
        $edition->addDistance($half);

        $distances = $edition->getDistances();

        $this->assertContains($marathon, $distances);
        $this->assertContains($half, $distances);
    }

    public function testDistancesPassedToConstructorAssignBidirectionalRelation(): void
    {
        $marathon = new Distance('Maraton', 42.195, 150.0);
        $edition = new Edition(new \DateTimeImmutable('+3 months'), null, [$marathon]);

        $reflection = new \ReflectionProperty($marathon, 'edition');

        $this->assertSame($edition, $reflection->getValue($marathon));
    }

    public function testCannotAddDuplicateDistanceToEdition(): void
    {
        $edition = new Edition(new \DateTimeImmutable('+3 months'));
        $edition->addDistance(new Distance('Maraton', 42.195, 150.0));

        $this->expectException(\DomainException::class);
        $edition->addDistance(new Distance('Maraton', 42.195, 150.0));
    }
}
