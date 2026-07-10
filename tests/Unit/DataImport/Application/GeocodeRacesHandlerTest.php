<?php

declare(strict_types=1);

namespace Tests\Unit\DataImport\Application;

use App\DataImport\Application\Handler\GeocodeRacesHandler;
use App\DataImport\Domain\Geocoding\GeocoderInterface;
use App\RaceCatalog\Domain\Event\RacesGeocoded;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Model\RaceId;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class GeocodeRacesHandlerTest extends TestCase
{
    private RaceRepositoryInterface&MockObject $repository;
    private GeocoderInterface&MockObject $geocoder;
    private MessageBusInterface&MockObject $messageBus;
    private GeocodeRacesHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RaceRepositoryInterface::class);
        $this->geocoder = $this->createMock(GeocoderInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->handler = new GeocodeRacesHandler(
            $this->repository,
            $this->geocoder,
            $this->messageBus,
        );
    }

    public function testGeocodesManyRacesWithoutCoordinatesUsingUniqueCities(): void
    {
        $raceOne = Race::create(RaceId::generate(), 'Race One', 'Warszawa', 'mazowieckie');
        $raceTwo = Race::create(RaceId::generate(), 'Race Two', 'Warszawa', 'mazowieckie');
        $raceThree = Race::create(RaceId::generate(), 'Race Three', 'Kraków', 'małopolskie');

        $this->repository->method('findWithoutCoordinates')->willReturn([$raceOne, $raceTwo, $raceThree]);
        $this->geocoder->expects($this->exactly(1))
            ->method('geocodeMany')
            ->with(['Warszawa', 'Kraków'])
            ->willReturn([
                'Warszawa' => ['lat' => 52.2297, 'lng' => 21.0122],
                'Kraków' => ['lat' => 50.0647, 'lng' => 19.9450],
            ]);
        $this->repository->expects($this->exactly(3))->method('save');
        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(RacesGeocoded::class))
            ->willReturn(new Envelope(new \stdClass()));

        $result = $this->handler->handle();

        $this->assertSame(3, $result->geocodedCount);
        $this->assertSame(0, $result->failedCount);
        $this->assertTrue($raceOne->hasCoordinates());
        $this->assertTrue($raceTwo->hasCoordinates());
        $this->assertTrue($raceThree->hasCoordinates());
    }

    public function testSkipsRacesThatAlreadyHaveCoordinatesWhenScopedByIds(): void
    {
        $raceWithCoords = Race::create(RaceId::generate(), 'Existing', 'Gdańsk', 'pomorskie');
        $raceWithCoords->setCoordinates(54.352, 18.6466);

        $raceWithoutCoords = Race::create(RaceId::generate(), 'New Race', 'Poznań', 'wielkopolskie');
        $raceId = $raceWithoutCoords->getId();

        $this->repository->method('findByIds')->willReturn([
            $raceWithCoords->getId()->toString() => $raceWithCoords,
            $raceId->toString() => $raceWithoutCoords,
        ]);
        $this->geocoder->expects($this->once())
            ->method('geocodeMany')
            ->with(['Poznań'])
            ->willReturn([
                'Poznań' => ['lat' => 52.4064, 'lng' => 16.9252],
            ]);
        $this->repository->expects($this->once())->method('save')->with($raceWithoutCoords);
        $this->messageBus->expects($this->once())->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $result = $this->handler->handle([$raceWithCoords->getId(), $raceId]);

        $this->assertSame(1, $result->geocodedCount);
        $this->assertTrue($raceWithoutCoords->hasCoordinates());
    }

    public function testDoesNotDispatchWhenNoRacesNeedGeocoding(): void
    {
        $this->repository->method('findWithoutCoordinates')->willReturn([]);
        $this->geocoder->expects($this->never())->method('geocode');
        $this->repository->expects($this->never())->method('save');
        $this->messageBus->expects($this->never())->method('dispatch');

        $result = $this->handler->handle();

        $this->assertSame(0, $result->geocodedCount);
        $this->assertSame(0, $result->failedCount);
    }

    public function testCountsFailedRacesWhenGeocoderReturnsNull(): void
    {
        $race = Race::create(RaceId::generate(), 'Unknown City Race', 'Nowhere', 'mazowieckie');

        $this->repository->method('findWithoutCoordinates')->willReturn([$race]);
        $this->geocoder->expects($this->once())->method('geocodeMany')->willReturn([]);
        $this->repository->expects($this->never())->method('save');
        $this->messageBus->expects($this->never())->method('dispatch');

        $result = $this->handler->handle();

        $this->assertSame(0, $result->geocodedCount);
        $this->assertSame(1, $result->failedCount);
        $this->assertFalse($race->hasCoordinates());
    }
}
