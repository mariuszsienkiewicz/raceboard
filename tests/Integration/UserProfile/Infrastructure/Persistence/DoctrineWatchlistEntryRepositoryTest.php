<?php

declare(strict_types=1);

namespace App\Tests\Integration\UserProfile\Infrastructure\Persistence;

use App\RaceCatalog\Domain\Model\RaceId;
use App\UserProfile\Domain\Model\UserId;
use App\UserProfile\Domain\Model\WatchlistEntry;
use App\UserProfile\Domain\Model\WatchlistEntryId;
use App\UserProfile\Domain\Repository\WatchlistEntryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineWatchlistEntryRepositoryTest extends KernelTestCase
{
    private WatchlistEntryRepositoryInterface $repository;
    private EntityManagerInterface $em;

    public function setUp(): void
    {
        self::bootKernel();
        $repository = self::getContainer()->get(WatchlistEntryRepositoryInterface::class);
        assert($repository instanceof WatchlistEntryRepositoryInterface);
        $this->repository = $repository;

        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        assert($em instanceof EntityManagerInterface);
        $this->em = $em;
    }

    public function testSavesAndFindsEntryByUser(): void
    {
        $entry = WatchlistEntry::create(
            WatchlistEntryId::generate(),
            UserId::generate(),
            RaceId::generate(),
        );

        $this->repository->save($entry);
        $this->em->clear();

        $entries = $this->repository->findByUser($entry->getUserId());
        $this->assertCount(1, $entries);
        $this->assertEquals($entry->getId(), $entries[0]->getId());
    }

    public function testFindsEntryByUserAndRace(): void
    {
        $entry = WatchlistEntry::create(
            WatchlistEntryId::generate(),
            UserId::generate(),
            RaceId::generate(),
        );

        $this->repository->save($entry);
        $this->em->clear();

        $foundEntry = $this->repository->findByUserAndRace($entry->getUserId(), $entry->getRaceId());
        $this->assertNotNull($foundEntry);
        $this->assertEquals($entry->getId(), $foundEntry->getId());
    }

    public function testFindByUserAndRaceReturnsNullWhenNotFound(): void
    {
        $entry = WatchlistEntry::create(
            WatchlistEntryId::generate(),
            UserId::generate(),
            RaceId::generate(),
        );

        $this->repository->save($entry);
        $this->em->clear();

        $foundEntry = $this->repository->findByUserAndRace($entry->getUserId(), RaceId::generate());
        $this->assertNull($foundEntry);
    }

    public function testFindByUserReturnsEmptyArrayWhenNoEntries(): void
    {
        $entries = $this->repository->findByUser(UserId::generate());
        $this->assertCount(0, $entries);
    }

    public function testRemovesEntry(): void
    {
        $entry = WatchlistEntry::create(
            WatchlistEntryId::generate(),
            UserId::generate(),
            RaceId::generate(),
        );

        $this->repository->save($entry);
        $this->em->clear();

        $foundEntry = $this->repository->findByUserAndRace($entry->getUserId(), $entry->getRaceId());
        $this->assertNotNull($foundEntry);

        $this->repository->remove($foundEntry);
        $this->em->clear();

        $deletedEntry = $this->repository->findByUserAndRace($entry->getUserId(), $entry->getRaceId());
        $this->assertNull($deletedEntry);
    }

    public function testFindByUserReturnsOnlyEntriesForGivenUser(): void
    {
        $userId1 = UserId::generate();
        $userId2 = UserId::generate();

        $entry1 = WatchlistEntry::create(
            WatchlistEntryId::generate(),
            $userId1,
            RaceId::generate(),
        );
        $entry2 = WatchlistEntry::create(
            WatchlistEntryId::generate(),
            $userId2,
            RaceId::generate(),
        );

        $this->repository->save($entry1);
        $this->repository->save($entry2);
        $this->em->clear();

        $entriesForUser1 = $this->repository->findByUser($userId1);
        $entriesForUser2 = $this->repository->findByUser($userId2);
        $this->assertCount(1, $entriesForUser1);
        $this->assertCount(1, $entriesForUser2);
        $this->assertEquals($entry1->getId(), $entriesForUser1[0]->getId());
        $this->assertEquals($entry2->getId(), $entriesForUser2[0]->getId());
    }
}
