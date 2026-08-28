<?php

declare(strict_types=1);

namespace App\UserProfile\Domain\Exception;

final class RaceNotFoundException extends \DomainException
{
    public static function forRace(string $raceId): self
    {
        return new self(\sprintf('Race "%s" not found.', $raceId));
    }
}
