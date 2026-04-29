<?php

declare(strict_types=1);

namespace App\RaceCatalog\Infrastructure\Persistence\Doctrine\Repository;

use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Model\RaceId;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineRaceRepository implements RaceRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Race $race): void
    {
        $this->entityManager->persist($race);
        $this->entityManager->flush();
    }

    public function findById(RaceId $id): ?Race
    {
        return $this->entityManager->find(Race::class, $id);
    }

    public function findBySlug(string $slug): ?Race
    {
        return $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Race::class, 'r')
            ->where('r.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return list<Race> */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Race::class)->findAll();
    }
}
