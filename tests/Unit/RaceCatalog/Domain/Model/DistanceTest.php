<?php

declare(strict_types=1);

namespace App\Tests\Unit\RaceCatalog\Domain\Model;

use App\RaceCatalog\Domain\Model\Distance;
use PHPUnit\Framework\TestCase;

final class DistanceTest extends TestCase
{
	public function testDistanceHasNameSetOnConstruction(): void
	{
		$distance = new Distance('Maraton', 42.195, 150.0);

		$this->assertSame('Maraton', $distance->getName());
	}

	public function testDistanceHasLengthSetOnConstruction(): void
	{
		$distance = new Distance('Półmaraton', 21.0975, 100.0);

		$this->assertSame(21.0975, $distance->getLengthInKm());
	}

	public function testDistanceHasNoPriceByDefault(): void
	{
		$distance = new Distance('10km', 10.0);

		$this->assertNull($distance->getPriceInPln());
	}

	public function testDistanceCanHavePrice(): void
	{
		$distance = new Distance('Maraton', 42.195, 149.99);

		$this->assertSame(149.99, $distance->getPriceInPln());
	}

	public function testIsMarathonReturnsTrueForMarathonDistance(): void
	{
		$distance = new Distance('Maraton', 42.195);

		$this->assertTrue($distance->isMarathon());
	}

	public function testIsMarathonReturnsTrueWithinAcceptedTolerance(): void
	{
		$distance = new Distance('Almost Maraton', 42.204);

		$this->assertTrue($distance->isMarathon());
	}

	public function testIsMarathonReturnsFalseOutsideAcceptedTolerance(): void
	{
		$distance = new Distance('Not Maraton', 42.206);

		$this->assertFalse($distance->isMarathon());
	}

	public function testIsMarathonReturnsFalseAtExactToleranceBoundary(): void
	{
		$distance = new Distance('Boundary Maraton', 42.2051);

		$this->assertFalse($distance->isMarathon());
	}

	public function testIsHalfMarathonReturnsTrueForHalfMarathonDistance(): void
	{
		$distance = new Distance('Półmaraton', 21.0975);

		$this->assertTrue($distance->isHalfMarathon());
	}

	public function testIsHalfMarathonReturnsTrueWithinAcceptedTolerance(): void
	{
		$distance = new Distance('Almost Półmaraton', 21.1064);

		$this->assertTrue($distance->isHalfMarathon());
	}

	public function testIsHalfMarathonReturnsFalseOutsideAcceptedTolerance(): void
	{
		$distance = new Distance('Not Półmaraton', 21.1080);

		$this->assertFalse($distance->isHalfMarathon());
	}

	public function testIsHalfMarathonReturnsFalseAtExactToleranceBoundary(): void
	{
		$distance = new Distance('Boundary Półmaraton', 21.1076);

		$this->assertFalse($distance->isHalfMarathon());
	}

	public function testDistanceCannotBeBothMarathonAndHalfMarathonInvariant(): void
	{
		$distance = new Distance('Maraton', 42.195);

		$this->assertTrue($distance->isMarathon());
		$this->assertFalse($distance->isHalfMarathon());
	}

}