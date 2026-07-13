<?php

declare(strict_types=1);

namespace App\UserProfile\Domain\Model;

use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;

class WatchlistEntry
{
    public function __construct(
        private readonly WatchlistEntryId $id,
        private readonly UserId $userId,
        private readonly RaceId $raceId,
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
    }

    public static function create(WatchlistEntryId $id, UserId $userId, RaceId $raceId): self
    {
        return new self($id, $userId, $raceId);
    }

    public function getId(): WatchlistEntryId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getRaceId(): RaceId
    {
        return $this->raceId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
