<?php

declare(strict_types=1);

namespace App\Review\Application\Command;

final readonly class RemoveReviewCommand
{
    public function __construct(
        public string $reviewId,
    ) {
    }
}
