<?php

declare(strict_types=1);

namespace App\Review\Domain\Repository;

use App\RaceCatalog\Domain\Model\RaceId;
use App\Review\Domain\Model\Review;
use App\UserProfile\Domain\Model\UserId;

interface ReviewRepositoryInterface
{
    public function save(Review $review): void;

    public function findByUserAndRace(UserId $userId, RaceId $raceId): ?Review;

    /** @return list<Review> */
    public function findByRace(RaceId $raceId): array;

    /** @return list<Review> */
    public function findByUser(UserId $userId): array;

    public function remove(Review $review): void;
}
