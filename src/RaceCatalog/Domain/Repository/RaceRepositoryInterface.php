<?php

declare(strict_types=1);

namespace App\RaceCatalog\Domain\Repository;

use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Model\RaceId;

interface RaceRepositoryInterface
{
    public function save(Race $race): void;

    public function findById(RaceId $id): ?Race;

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
    public function findAll(): array;
}
