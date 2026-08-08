<?php

declare(strict_types=1);

namespace App\DataImport\Application;

use App\DataImport\Domain\RawRaceData;
use App\RaceCatalog\Domain\Event\RacesImported;
use App\RaceCatalog\Domain\Exception\DuplicateEditionException;
use App\RaceCatalog\Domain\Model\Distance;
use App\RaceCatalog\Domain\Model\Edition;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Search\Domain\SearchIndexInterface;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Slugifier;
use Symfony\Component\Messenger\MessageBusInterface;

class ImportRacesHandler
{
    public function __construct(private RaceRepositoryInterface $raceRepository, private DuplicateDetector $duplicateDetector, private SearchIndexInterface $searchIndex, private MessageBusInterface $eventBus)
    {
    }

    /**
     * @param list<RawRaceData> $racesData
     */
    public function handle(array $racesData): ImportResult
    {
        $importResult = new ImportResult();
        $newRaceIds = [];

        foreach ($racesData as $rawRaceData) {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $rawRaceData->date);
            if (false === $date) {
                $importResult->incrementSkipped();
                continue;
            }

            $slug = Slugifier::slugify($rawRaceData->name);
            $existing = $this->raceRepository->findBySlug($slug);
            if ($existing) {
                if ($this->enrichRace($existing, $rawRaceData, $date)) {
                    $importResult->incrementUpdated();
                } else {
                    $importResult->incrementSkipped();
                }
                continue;
            }

            $candidates = $this->raceRepository->findSimilar($rawRaceData->date, $rawRaceData->city);
            $duplicate = $this->duplicateDetector->findDuplicate($rawRaceData->name, $candidates);
            if (null !== $duplicate) {
                if ($this->enrichRace($duplicate, $rawRaceData, $date)) {
                    $importResult->incrementUpdated();
                } else {
                    $importResult->incrementSkipped();
                }
                continue;
            }

            $race = Race::create(
                RaceId::generate(),
                $rawRaceData->name,
                $rawRaceData->city,
                $rawRaceData->voivodeship,
            );

            $edition = new Edition($date, $rawRaceData->registrationUrl ?: null);
            foreach ($rawRaceData->distances as $distanceData) {
                $edition->addDistance(new Distance(
                    $distanceData['name'],
                    $distanceData['lengthInKm'],
                    $distanceData['priceInPln'],
                ));
            }

            $race->addEdition($edition);
            $this->raceRepository->save($race);
            $this->searchIndex->indexRace($race);

            $newRaceIds[] = $race->getId();
            $importResult->incrementImported();
        }

        if ([] !== $newRaceIds) {
            $this->eventBus->dispatch(new RacesImported($newRaceIds));
        }

        return $importResult;
    }

    private function enrichRace(Race $race, RawRaceData $data, \DateTimeImmutable $date): bool
    {
        $changed = false;

        if ('' === $race->getVoivodeship() && '' !== $data->voivodeship) {
            $race->updateVoivodeship($data->voivodeship);
            $changed = true;
        }

        $edition = $race->findEditionByDate($date);
        if (null === $edition) {
            $edition = new Edition($date, $data->registrationUrl ?: null);
            foreach ($data->distances as $distanceData) {
                $edition->addDistance(new Distance(
                    $distanceData['name'],
                    $distanceData['lengthInKm'],
                    $distanceData['priceInPln'],
                ));
            }

            try {
                $race->addEdition($edition);
                $changed = true;
            } catch (DuplicateEditionException) {
                // Same calendar year already has an edition outside the ±1 day match window.
            }
        } else {
            foreach ($data->distances as $distanceData) {
                if (!$edition->hasDistance($distanceData['lengthInKm'])) {
                    $edition->addDistance(new Distance(
                        $distanceData['name'],
                        $distanceData['lengthInKm'],
                        $distanceData['priceInPln'],
                    ));
                    $changed = true;
                }
            }
        }

        if ($changed) {
            $this->raceRepository->save($race);
            $this->searchIndex->indexRace($race);
        }

        return $changed;
    }
}
