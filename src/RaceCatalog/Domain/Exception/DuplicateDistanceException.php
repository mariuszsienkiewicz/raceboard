<?php

namespace App\RaceCatalog\Domain\Exception;

class DuplicateDistanceException extends \DomainException
{
    public static function forEdition(string $distanceName): self
    {
        return new self(\sprintf('Distance "%s" already exists in this edition.', $distanceName));
    }
}
