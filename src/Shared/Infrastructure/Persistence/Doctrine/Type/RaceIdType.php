<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Type;

use App\Shared\Domain\Model\RaceId;

final class RaceIdType extends AbstractIdType
{
    public function getName(): string
    {
        return 'race_id';
    }

    protected function getIdClass(): string
    {
        return RaceId::class;
    }
}
