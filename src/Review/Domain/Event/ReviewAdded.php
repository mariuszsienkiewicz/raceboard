<?php

declare(strict_types=1);

namespace App\Review\Domain\Event;

use App\Shared\Domain\Model\RaceId;

final readonly class ReviewAdded
{
    public function __construct(
        public RaceId $raceId,
    ) {
    }
}
