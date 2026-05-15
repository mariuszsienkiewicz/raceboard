<?php

declare(strict_types=1);

namespace App\RaceCatalog\Infrastructure\Api;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;

/** @implements ProviderInterface<\App\RaceCatalog\Domain\Model\Race> */
class RaceCollectionProvider implements ProviderInterface
{
    public function __construct(private RaceRepositoryInterface $repository)
    {
    }

    /**
     * @return \App\RaceCatalog\Domain\Model\Race[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return $this->repository->findAll();
    }
}
