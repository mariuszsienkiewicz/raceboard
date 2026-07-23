<?php

declare(strict_types=1);

namespace App\RaceCatalog\Infrastructure\Persistence\Doctrine\Repository;

use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

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

    public function exists(RaceId $id): bool
    {
        $count = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(r)')
            ->from(Race::class, 'r')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $races = $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Race::class, 'r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($races as $race) {
            $indexed[$race->getId()->toString()] = $race;
        }

        return $indexed;
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
    public function findSimilar(string $date, string $city): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Race::class, 'r')
            ->join('r.editions', 'e')
            ->where('e.date BETWEEN :dateFrom AND :dateTo')
            ->andWhere('LOWER(r.city) = LOWER(:city)')
            ->setParameter('dateFrom', (new \DateTimeImmutable($date))->modify('-1 day'))
            ->setParameter('dateTo', (new \DateTimeImmutable($date))->modify('+1 day'))
            ->setParameter('city', $city)
            ->getQuery()
            ->getResult();
    }

    /** @return list<Race> */
    public function findWithoutCoordinates(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Race::class, 'r')
            ->where('r.latitude IS NULL OR r.longitude IS NULL')
            ->getQuery()
            ->getResult();
    }

    /** @return list<Race> */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Race::class)->findAll();
    }

    public function findPaginatedWithDetails(int $limit, int $offset): \Traversable
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('r')
            ->addSelect('e')
            ->addSelect('d')
            ->from(Race::class, 'r')
            ->leftJoin('r.editions', 'e')
            ->leftJoin('e.distances', 'd')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function count(): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(r)')
            ->from(Race::class, 'r')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
