<?php

declare(strict_types=1);

namespace App\Review\Domain\Exception;

final class ReviewAlreadyExistsException extends \DomainException
{
    public function __construct(string $raceId)
    {
        parent::__construct(sprintf('User has already reviewed race "%s".', $raceId));
    }
}
