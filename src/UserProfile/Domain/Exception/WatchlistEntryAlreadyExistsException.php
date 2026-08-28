<?php

declare(strict_types=1);

namespace App\UserProfile\Domain\Exception;

final class WatchlistEntryAlreadyExistsException extends \DomainException
{
    public static function forRace(string $raceId): self
    {
        return new self(\sprintf('Watchlist entry for race "%s" already exists.', $raceId));
    }
}
