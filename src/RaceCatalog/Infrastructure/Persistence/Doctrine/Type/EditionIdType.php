<?php

namespace App\RaceCatalog\Infrastructure\Persistence\Doctrine\Type;

use App\RaceCatalog\Domain\Model\EditionId;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractIdType;

final class EditionIdType extends AbstractIdType
{
    public function getName(): string { return 'edition_id'; }
    protected function getIdClass(): string { return EditionId::class; }
}
