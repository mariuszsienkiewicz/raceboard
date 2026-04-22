<?php

declare(strict_types=1);

namespace App\RaceCatalog\Domain\Exception;

final class EditionInThePastException extends \DomainException
{
    public static function forRace(string $raceName, \DateTimeImmutable $date): self
    {
        return new self(\sprintf(
            'Cannot add edition on %s to race "%s": date is in the past.',
            $date->format('Y-m-d'),
            $raceName,
        ));
    }
}