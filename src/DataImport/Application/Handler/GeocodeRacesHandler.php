<?php

declare(strict_types=1);

namespace App\DataImport\Application\Handler;

use App\DataImport\Application\GeocodeRacesResult;
use App\DataImport\Domain\Geocoding\GeocoderInterface;
use App\RaceCatalog\Domain\Event\RacesGeocoded;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use Symfony\Component\Messenger\MessageBusInterface;

class GeocodeRacesHandler
{
    private const GEOCODE_BATCH_SIZE = 25;

    public function __construct(
        private RaceRepositoryInterface $raceRepository,
        private GeocoderInterface $geocoder,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @param list<RaceId>|null $raceIds null = all races without coordinates (backfill)
     */
    public function handle(?array $raceIds = null): GeocodeRacesResult
    {
        $races = null !== $raceIds
            ? array_values($this->raceRepository->findByIds($raceIds))
            : $this->raceRepository->findWithoutCoordinates();

        $racesToGeocode = array_values(array_filter(
            $races,
            static fn (Race $race): bool => !$race->hasCoordinates(),
        ));

        if ([] === $racesToGeocode) {
            return new GeocodeRacesResult();
        }

        $coordsByCity = $this->resolveCoordinatesByCity($racesToGeocode);

        $geocodedIds = [];
        $failed = 0;

        foreach ($racesToGeocode as $race) {
            $coords = $coordsByCity[$race->getCity()] ?? null;
            if (null === $coords) {
                ++$failed;
                continue;
            }

            $race->setCoordinates($coords['lat'], $coords['lng']);
            $this->raceRepository->save($race);
            $geocodedIds[] = $race->getId();
        }

        if ([] !== $geocodedIds) {
            $this->messageBus->dispatch(new RacesGeocoded($geocodedIds));
        }

        return new GeocodeRacesResult(\count($geocodedIds), $failed);
    }

    /**
     * @param list<Race> $races
     *
     * @return array<string, array{lat: float, lng: float}>
     */
    private function resolveCoordinatesByCity(array $races): array
    {
        $cities = [];
        foreach ($races as $race) {
            $cities[$race->getCity()] = true;
        }

        $uniqueCities = array_keys($cities);

        $coordsByCity = [];
        foreach (array_chunk($uniqueCities, self::GEOCODE_BATCH_SIZE) as $chunk) {
            $coordsByCity += $this->geocoder->geocodeMany($chunk);
        }

        return $coordsByCity;
    }
}
