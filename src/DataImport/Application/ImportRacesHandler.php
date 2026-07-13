<?php

declare(strict_types=1);

namespace App\DataImport\Application;

use App\DataImport\Domain\RawRaceData;
use App\RaceCatalog\Domain\Event\RacesImported;
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
    public function __construct(private RaceRepositoryInterface $raceRepository, private DuplicateDetector $duplicateDetector, private SearchIndexInterface $searchIndex, private MessageBusInterface $messageBus)
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
                $this->enrichRace($existing, $rawRaceData, $date);
                $importResult->incrementUpdated();
                continue;
            }

            $candidates = $this->raceRepository->findSimilar($rawRaceData->date, $rawRaceData->city);
            $duplicate = $this->duplicateDetector->findDuplicate($rawRaceData->name, $candidates);
            if (null !== $duplicate) {
                $this->enrichRace($duplicate, $rawRaceData, $date);
                $importResult->incrementUpdated();
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
            $this->messageBus->dispatch(new RacesImported($newRaceIds));
        }

        return $importResult;
    }

    private function enrichRace(Race $race, RawRaceData $data, \DateTimeImmutable $date): void
    {
        $changed = false;

        if ('' === $race->getVoivodeship() && '' !== $data->voivodeship) {
            $race->updateVoivodeship($data->voivodeship);
            $changed = true;
        }

        $edition = $race->findEditionByDate($date);
        if (null !== $edition) {
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
    }
}
