<?php

declare(strict_types=1);

namespace App\RaceCatalog\Infrastructure\Api;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;

/** @implements ProviderInterface<\App\RaceCatalog\Domain\Model\Race> */
class RaceCollectionProvider implements ProviderInterface
{
    public function __construct(private RaceRepositoryInterface $repository, private Pagination $pagination)
    {
    }

    /** @return TraversablePaginator<Race> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PaginatorInterface
    {
        [$page, $offset, $limit] = $this->pagination->getPagination($operation, $context);

        $count = $this->repository->count();
        $races = $this->repository->findPaginatedWithDetails($limit, $offset);

        return new TraversablePaginator($races, $page, $limit, $count);
    }
}
