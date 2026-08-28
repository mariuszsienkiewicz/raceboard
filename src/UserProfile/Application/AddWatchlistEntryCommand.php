<?php

declare(strict_types=1);

namespace App\UserProfile\Application;

final readonly class AddWatchlistEntryCommand
{
    public function __construct(
        public string $userId,
        public string $raceId,
    ) {
    }
}
