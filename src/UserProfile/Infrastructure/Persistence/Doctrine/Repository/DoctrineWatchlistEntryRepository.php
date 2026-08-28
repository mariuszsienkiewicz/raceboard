<?php

declare(strict_types=1);

namespace App\UserProfile\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use App\UserProfile\Domain\Exception\WatchlistEntryAlreadyExistsException;
use App\UserProfile\Domain\Model\WatchlistEntry;
use App\UserProfile\Domain\Repository\WatchlistEntryRepositoryInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineWatchlistEntryRepository implements WatchlistEntryRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(WatchlistEntry $watchlistEntry): void
    {
        try {
            $this->entityManager->persist($watchlistEntry);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            throw WatchlistEntryAlreadyExistsException::forRace($watchlistEntry->getRaceId()->toString());
        }
    }

    public function findByUser(UserId $userId): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('w')
            ->from(WatchlistEntry::class, 'w')
            ->where('w.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndRace(UserId $userId, RaceId $raceId): ?WatchlistEntry
    {
        return $this->entityManager->createQueryBuilder()
            ->select('w')
            ->from(WatchlistEntry::class, 'w')
            ->where('w.userId = :userId')
            ->andWhere('w.raceId = :raceId')
            ->setParameter('userId', $userId)
            ->setParameter('raceId', $raceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWatchedRaceIds(UserId $userId, array $raceIds): array
    {
        if ([] === $raceIds) {
            return [];
        }

        $result = $this->entityManager->createQueryBuilder()
            ->select('w.raceId')
            ->from(WatchlistEntry::class, 'w')
            ->where('w.userId = :userId')
            ->andWhere('w.raceId IN (:raceIds)')
            ->setParameter('userId', $userId)
            ->setParameter('raceIds', $raceIds)
            ->getQuery()
            ->getResult();

        $watchedRaceIds = [];
        foreach ($result as $row) {
            $watchedRaceIds[] = $row['raceId'];
        }

        return $watchedRaceIds;
    }

    public function findUserIdsByCity(string $city): array
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('DISTINCT w.userId')
            ->from(WatchlistEntry::class, 'w')
            ->where('w.raceId IN (
                SELECT r.id FROM App\RaceCatalog\Domain\Model\Race r WHERE LOWER(r.city) = LOWER(:city)
            )')
            ->setParameter('city', $city)
            ->getQuery()
            ->getResult();

        $userIds = [];
        foreach ($result as $row) {
            $userIds[] = $row['userId'];
        }

        return $userIds;
    }

    public function remove(WatchlistEntry $entry): void
    {
        $this->entityManager->remove($entry);
        $this->entityManager->flush();
    }
}
