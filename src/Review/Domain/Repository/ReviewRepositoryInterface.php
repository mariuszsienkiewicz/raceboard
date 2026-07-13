<?php

declare(strict_types=1);

namespace App\Review\Domain\Repository;

use App\Review\Domain\Model\Review;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;

interface ReviewRepositoryInterface
{
    public function save(Review $review): void;

    public function findByUserAndRace(UserId $userId, RaceId $raceId): ?Review;

    /** @return list<Review> */
    public function findByRace(RaceId $raceId, int $limit, int $offset): array;

    /** @return list<Review> */
    public function findByUser(UserId $userId): array;

    public function countByRace(RaceId $raceId): int;

    public function getAverageRating(RaceId $raceId): ?float;

    public function remove(Review $review): void;
}
