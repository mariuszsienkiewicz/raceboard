<?php

declare(strict_types=1);

namespace App\Review\Infrastructure\Persistence\Doctrine\Type;

use App\Review\Domain\Model\ReviewId;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractIdType;

final class ReviewIdType extends AbstractIdType
{
    public function getName(): string
    {
        return 'review_id';
    }

    protected function getIdClass(): string
    {
        return ReviewId::class;
    }
}
