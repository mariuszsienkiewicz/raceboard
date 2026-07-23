<?php

declare(strict_types=1);

namespace App\RaceCatalog\Domain\Repository;

use App\RaceCatalog\Domain\Model\Race;
use App\Shared\Domain\Model\RaceId;

interface RaceRepositoryInterface
{
    public function save(Race $race): void;

    public function findById(RaceId $id): ?Race;

    public function exists(RaceId $id): bool;

    /**
     * @param array<RaceId> $ids
     *
     * @return array<string, Race>
     */
    public function findByIds(array $ids): array;

    public function findBySlug(string $slug): ?Race;

    /** @return list<Race> */
    public function findSimilar(string $date, string $city): array;

    /** @return list<Race> */
    public function findWithoutCoordinates(): array;

    /** @return list<Race> */
    public function findAll(): array;

    /** @return \Traversable<Race> */
    public function findPaginatedWithDetails(int $limit, int $offset): \Traversable;

    public function count(): int;
}
