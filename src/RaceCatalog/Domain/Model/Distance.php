<?php

declare(strict_types=1);

namespace App\RaceCatalog\Domain\Model;

class Distance
{
    private ?DistanceId $id = null;
    private ?Edition $edition = null;

    public function __construct(
        private readonly string $name,
        private readonly float $lengthInKm,
        private readonly ?float $priceInPln = null,
    ) {
        $this->id = DistanceId::generate();
    }

    public function assignEdition(Edition $edition): void
    {
        $this->edition = $edition;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLengthInKm(): float
    {
        return $this->lengthInKm;
    }

    public function getPriceInPln(): ?float
    {
        return $this->priceInPln;
    }

    public function isMarathon(): bool
    {
        return abs($this->lengthInKm - 42.195) < 0.01;
    }

    public function isHalfMarathon(): bool
    {
        return abs($this->lengthInKm - 21.0975) < 0.01;
    }
}
