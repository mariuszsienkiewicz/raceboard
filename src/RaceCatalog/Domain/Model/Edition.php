<?php

declare(strict_types=1);

namespace App\RaceCatalog\Domain\Model;

use App\RaceCatalog\Domain\Exception\DuplicateDistanceException;

class Edition
{
    private ?EditionId $id = null;
    private ?Race $race = null;

    /** @var list<Distance> */
    private array $distances;

    /**
     * @param list<Distance> $distances
     */
    public function __construct(
        private readonly \DateTimeImmutable $date,
        private readonly ?string $registrationUrl = null,
        array $distances = [],
    ) {
        $this->id = EditionId::generate();
        $this->distances = [];

        foreach ($distances as $distance) {
            $this->addDistance($distance);
        }
    }

    public function addDistance(Distance $distance): void
    {
        foreach ($this->distances as $existingDistance) {
            $sameName = $existingDistance->getName() === $distance->getName();
            $sameLength = abs($existingDistance->getLengthInKm() - $distance->getLengthInKm()) < 0.00001;
            if ($sameName && $sameLength) {
                throw DuplicateDistanceException::forEdition($distance->getName());
            }
        }

        $distance->assignEdition($this);
        $this->distances[] = $distance;
    }

    public function assignRace(Race $race): void
    {
        $this->race = $race;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getRegistrationUrl(): ?string
    {
        return $this->registrationUrl;
    }

    /** @return list<Distance> */
    public function getDistances(): array
    {
        return $this->distances;
    }
}