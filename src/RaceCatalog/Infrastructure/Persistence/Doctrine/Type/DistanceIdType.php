<?php

namespace App\RaceCatalog\Infrastructure\Persistence\Doctrine\Type;

use App\RaceCatalog\Domain\Model\DistanceId;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractIdType;

final class DistanceIdType extends AbstractIdType
{
    public function getName(): string { return 'distance_id'; }
    protected function getIdClass(): string { return DistanceId::class; }
}
