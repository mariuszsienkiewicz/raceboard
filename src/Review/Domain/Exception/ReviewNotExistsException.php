<?php

declare(strict_types=1);

namespace App\Review\Domain\Exception;

final class ReviewNotExistsException extends \DomainException
{
    public function __construct(string $reviewId)
    {
        parent::__construct(sprintf('Review "%s" not found.', $reviewId));
    }
}
