<?php

declare(strict_types=1);

namespace App\Tests\Integration\UserProfile\Infrastructure\Persistence;

use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Model\WatchlistEntry;
use App\UserProfile\Domain\Model\WatchlistEntryId;
use App\UserProfile\Domain\Repository\UserRepositoryInterface;
use App\UserProfile\Domain\Repository\WatchlistEntryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineWatchlistEntryRepositoryTest extends KernelTestCase
{
    private WatchlistEntryRepositoryInterface $repository;
    private EntityManagerInterface $em;
    private RaceRepositoryInterface $raceRepository;
    private UserRepositoryInterface $userRepository;

    public function setUp(): void
    {
        self::bootKernel();
        $repository = self::getContainer()->get(WatchlistEntryRepositoryInterface::class);
        assert($repository instanceof WatchlistEntryRepositoryInterface);
        $this->repository = $repository;

        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        assert($em instanceof EntityManagerInterface);

        $raceRepository = self::getContainer()->get(RaceRepositoryInterface::class);
        assert($raceRepository instanceof RaceRepositoryInterface);
        $this->raceRepository = $raceRepository;

        $userRepository = self::getContainer()->get(UserRepositoryInterface::class);
        assert($userRepository instanceof UserRepositoryInterface);
        $this->userRepository = $userRepository;

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

    public function testFindUserIdsByCityReturnsUsersWatchingRacesInCity(): void
    {
        // races
        $race1Id = RaceId::generate();
        $race1 = Race::create(
            $race1Id,
            'Test Race',
            'Warsaw',
            'Masovian',
        );

        $race2Id = RaceId::generate();
        $race2 = Race::create(
            $race2Id,
            'Test Race 2',
            'Warsaw',
            'Masovian',
        );

        $this->raceRepository->save($race1);
        $this->raceRepository->save($race2);

        // users
        $user1 = User::create(
            UserId::generate(),
            'test@example.com',
            'password',
            'John Doe',
        );
        $user2 = User::create(
            UserId::generate(),
            'test2@example.com',
            'password',
            'Jane Doe',
        );
        $this->userRepository->save($user1);
        $this->userRepository->save($user2);

        // watchlist entry for user
        $entry1 = WatchlistEntry::create(WatchlistEntryId::generate(), $user1->getId(), $race1Id);
        $entry2 = WatchlistEntry::create(WatchlistEntryId::generate(), $user1->getId(), $race2Id);
        $entry3 = WatchlistEntry::create(WatchlistEntryId::generate(), $user2->getId(), $race1Id);

        $this->repository->save($entry1);
        $this->repository->save($entry2);
        $this->repository->save($entry3);
        $this->em->clear();

        // find user ids by city
        $userIds = $this->repository->findUserIdsByCity('Warsaw');

        // check that both users are returned
        $userIdStrings = array_map(fn (UserId $id) => $id->toString(), $userIds);
        $this->assertContains($user1->getId()->toString(), $userIdStrings);
        $this->assertContains($user2->getId()->toString(), $userIdStrings);

        // check that the correct number of users are returned
        $this->assertCount(2, $userIds);
    }

    public function testFindUserIdsByCityReturnsEmptyForUnwatchedCity(): void
    {
        $raceId = RaceId::generate();
        $race = Race::create($raceId, 'Krakow Race', 'Krakow', 'Lesser Poland');

        $this->raceRepository->save($race);
        $this->em->clear();

        $userIds = $this->repository->findUserIdsByCity('Krakow');

        $this->assertCount(0, $userIds);
    }

    public function testFindUserIdsByCityReturnsDistinctUsers(): void
    {
        $race1Id = RaceId::generate();
        $race2Id = RaceId::generate();
        $race1 = Race::create($race1Id, 'Race 1', 'Gdansk', 'Pomeranian');
        $race2 = Race::create($race2Id, 'Race 2', 'Gdansk', 'Pomeranian');

        $this->raceRepository->save($race1);
        $this->raceRepository->save($race2);

        $user = User::create(UserId::generate(), 'test@example.com', 'password', 'John Doe');
        $this->userRepository->save($user);

        $this->repository->save(WatchlistEntry::create(WatchlistEntryId::generate(), $user->getId(), $race1Id));
        $this->repository->save(WatchlistEntry::create(WatchlistEntryId::generate(), $user->getId(), $race2Id));
        $this->em->clear();

        $userIds = $this->repository->findUserIdsByCity('Gdansk');

        $this->assertCount(1, $userIds);
    }
}
