<?php

declare(strict_types=1);

namespace App\Review\Infrastructure\Persistence\Doctrine\Repository;

use App\Review\Domain\Model\Review;
use App\Review\Domain\Repository\ReviewRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineReviewRepository implements ReviewRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Review $review): void
    {
        $this->entityManager->persist($review);
        $this->entityManager->flush();
    }

    public function findByRace(RaceId $raceId, int $limit, int $offset): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Review::class, 'r')
            ->where('r.raceId = :raceId')
            ->setParameter('raceId', $raceId)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function findByUser(UserId $userId): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Review::class, 'r')
            ->where('r.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndRace(UserId $userId, RaceId $raceId): ?Review
    {
        return $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Review::class, 'r')
            ->where('r.userId = :userId')
            ->andWhere('r.raceId = :raceId')
            ->setParameter('userId', $userId)
            ->setParameter('raceId', $raceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countByRace(RaceId $raceId): int
    {
        $count = $this->entityManager->createQueryBuilder()
            ->select('COUNT(r)')
            ->from(Review::class, 'r')
            ->where('r.raceId = :raceId')
            ->setParameter('raceId', $raceId)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return (int) $count;
    }

    public function getAverageRating(RaceId $raceId): ?float
    {
        $average = $this->entityManager->createQueryBuilder()
            ->select('AVG(r.rating)')
            ->from(Review::class, 'r')
            ->where('r.raceId = :raceId')
            ->setParameter('raceId', $raceId)
            ->getQuery()
            ->getSingleScalarResult();

        return null === $average ? null : (float) $average;
    }

    public function remove(Review $review): void
    {
        $this->entityManager->remove($review);
        $this->entityManager->flush();
    }
}
