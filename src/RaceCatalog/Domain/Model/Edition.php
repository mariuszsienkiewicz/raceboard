<?php
// src/RaceCatalog/Domain/Model/Edition.php

declare(strict_types=1);

namespace App\RaceCatalog\Domain\Model;

class Edition
{
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
        $this->distances = $distances;
    }

    public function addDistance(Distance $distance): void
    {
        $this->distances[] = $distance;
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