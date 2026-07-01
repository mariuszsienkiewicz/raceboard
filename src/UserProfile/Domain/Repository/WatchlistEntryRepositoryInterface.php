<?php

declare(strict_types=1);

namespace App\UserProfile\Domain\Repository;

use App\RaceCatalog\Domain\Model\RaceId;
use App\UserProfile\Domain\Model\UserId;
use App\UserProfile\Domain\Model\WatchlistEntry;

interface WatchlistEntryRepositoryInterface
{
    public function save(WatchlistEntry $entry): void;

    public function remove(WatchlistEntry $entry): void;

    public function findByUserAndRace(UserId $userId, RaceId $raceId): ?WatchlistEntry;

    /** @return list<UserId> */
    public function findUserIdsByCity(string $city): array;

    /** @return list<WatchlistEntry> */
    public function findByUser(UserId $userId): array;
}
