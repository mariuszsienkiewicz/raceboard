<?php

declare(strict_types=1);

namespace App\DataImport\Application;

use App\DataImport\Domain\RawRaceData;
use App\RaceCatalog\Domain\Model\Edition;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Model\RaceId;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Shared\Domain\Slugifier;

class ImportRacesHandler
{
    public function __construct(private RaceRepositoryInterface $raceRepository)
    {
    }

    /**
     * @param list<RawRaceData> $racesData
     */
    public function handle(array $racesData): ImportResult
    {
        $importResult = new ImportResult();

        foreach ($racesData as $rawRaceData) {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $rawRaceData->date);
            if (false === $date) {
                $importResult->incrementSkipped();
                continue;
            }

            $slug = Slugifier::slugify($rawRaceData->name);
            $existing = $this->raceRepository->findBySlug($slug);
            if ($existing) {
                $importResult->incrementSkipped();
                continue;
            }

            $race = Race::create(
                RaceId::generate(),
                $rawRaceData->name,
                $rawRaceData->city,
                $rawRaceData->voivodeship,
            );

            $edition = new Edition($date, $rawRaceData->registrationUrl ?: null);
            $race->addEdition($edition);
            $this->raceRepository->save($race);
            $importResult->incrementImported();
        }

        return $importResult;
    }
}
