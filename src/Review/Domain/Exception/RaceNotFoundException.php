<?php

declare(strict_types=1);

namespace App\Review\Domain\Exception;

final class RaceNotFoundException extends \DomainException
{
    public function __construct(string $raceId)
    {
        parent::__construct(sprintf('Race "%s" not found.', $raceId));
    }
}
