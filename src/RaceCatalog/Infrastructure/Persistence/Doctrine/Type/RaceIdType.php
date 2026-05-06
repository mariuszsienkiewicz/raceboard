<?php

declare(strict_types=1);

namespace App\RaceCatalog\Infrastructure\Persistence\Doctrine\Type;

use App\RaceCatalog\Domain\Model\RaceId;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractIdType;

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
