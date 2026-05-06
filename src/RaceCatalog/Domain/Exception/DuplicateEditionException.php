<?php

declare(strict_types=1);

namespace App\RaceCatalog\Domain\Exception;

final class DuplicateEditionException extends \DomainException
{
    public static function forYear(string $raceName, int $year): self
    {
        return new self(\sprintf(
            'Race "%s" already has an edition in year %d.',
            $raceName,
            $year,
        ));
    }
}
