<?php

declare(strict_types=1);

namespace App\RaceCatalog\Infrastructure\Api;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Model\RaceId;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;

/** @implements ProviderInterface<Race> */
class RaceItemProvider implements ProviderInterface
{
    public function __construct(private RaceRepositoryInterface $repository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Race
    {
        return $this->repository->findById(RaceId::fromString($uriVariables['id']));
    }
}
