<?php

declare(strict_types=1);

namespace App\Review\Domain\Model;

use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;

class Review
{
    public function __construct(
        private readonly ReviewId $id,
        private readonly UserId $userId,
        private readonly RaceId $raceId,
        private int $rating,
        private string $comment = '',
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5');
        }
    }

    public static function create(ReviewId $id, UserId $userId, RaceId $raceId, int $rating, string $comment = ''): self
    {
        return new self($id, $userId, $raceId, $rating, $comment);
    }

    public function getId(): ReviewId
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

    public function getRating(): int
    {
        return $this->rating;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
