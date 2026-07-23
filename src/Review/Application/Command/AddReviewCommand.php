<?php

declare(strict_types=1);

namespace App\Review\Application\Command;

final readonly class AddReviewCommand
{
    public function __construct(
        public string $raceId,
        public string $userId,
        public int $rating,
        public string $comment,
    ) {
    }
}
