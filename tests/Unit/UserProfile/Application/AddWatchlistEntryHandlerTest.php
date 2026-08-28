<?php

declare(strict_types=1);

namespace App\Tests\Unit\UserProfile\Application;

use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use App\UserProfile\Application\AddWatchlistEntryCommand;
use App\UserProfile\Application\AddWatchlistEntryHandler;
use App\UserProfile\Domain\Exception\RaceNotFoundException;
use App\UserProfile\Domain\Exception\WatchlistEntryAlreadyExistsException;
use App\UserProfile\Domain\Model\WatchlistEntry;
use App\UserProfile\Domain\Model\WatchlistEntryId;
use App\UserProfile\Domain\Repository\WatchlistEntryRepositoryInterface;
use App\UserProfile\Domain\Service\RaceExistenceCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AddWatchlistEntryHandlerTest extends TestCase
{
    private WatchlistEntryRepositoryInterface&MockObject $watchlistEntryRepository;
    private RaceExistenceCheckerInterface&MockObject $raceExistenceChecker;
    private AddWatchlistEntryHandler $handler;

    protected function setUp(): void
    {
        $this->watchlistEntryRepository = $this->createMock(WatchlistEntryRepositoryInterface::class);
        $this->raceExistenceChecker = $this->createMock(RaceExistenceCheckerInterface::class);
        $this->handler = new AddWatchlistEntryHandler(
            $this->watchlistEntryRepository,
            $this->raceExistenceChecker,
        );
    }

    public function testExceptionIsThrownIfRaceDoesNotExist(): void
    {
        $this->expectException(RaceNotFoundException::class);

        $this->raceExistenceChecker->expects($this->once())
            ->method('exists')
            ->willReturn(false);
        $this->watchlistEntryRepository->expects($this->never())
            ->method('findByUserAndRace');
        $this->watchlistEntryRepository->expects($this->never())
            ->method('save');

        ($this->handler)(new AddWatchlistEntryCommand(
            UserId::generate()->toString(),
            RaceId::generate()->toString(),
        ));
    }

    public function testExceptionIsThrownIfWatchlistEntryAlreadyExists(): void
    {
        $this->expectException(WatchlistEntryAlreadyExistsException::class);

        $userId = UserId::generate();
        $raceId = RaceId::generate();

        $this->raceExistenceChecker->expects($this->once())
            ->method('exists')
            ->with($raceId)
            ->willReturn(true);
        $this->watchlistEntryRepository->expects($this->once())
            ->method('findByUserAndRace')
            ->with($userId, $raceId)
            ->willReturn(WatchlistEntry::create(WatchlistEntryId::generate(), $userId, $raceId));
        $this->watchlistEntryRepository->expects($this->never())
            ->method('save');

        ($this->handler)(new AddWatchlistEntryCommand(
            $userId->toString(),
            $raceId->toString(),
        ));
    }

    public function testExceptionIsThrownOnConcurrentDuplicateSave(): void
    {
        $this->expectException(WatchlistEntryAlreadyExistsException::class);

        $userId = UserId::generate();
        $raceId = RaceId::generate();

        $this->raceExistenceChecker->expects($this->once())
            ->method('exists')
            ->with($raceId)
            ->willReturn(true);
        $this->watchlistEntryRepository->expects($this->once())
            ->method('findByUserAndRace')
            ->with($userId, $raceId)
            ->willReturn(null);
        $this->watchlistEntryRepository->expects($this->once())
            ->method('save')
            ->willThrowException(WatchlistEntryAlreadyExistsException::forRace($raceId->toString()));

        ($this->handler)(new AddWatchlistEntryCommand(
            $userId->toString(),
            $raceId->toString(),
        ));
    }

    public function testWatchlistEntryIsSavedWhenRaceExists(): void
    {
        $userId = UserId::generate();
        $raceId = RaceId::generate();

        $this->raceExistenceChecker->expects($this->once())
            ->method('exists')
            ->with($raceId)
            ->willReturn(true);
        $this->watchlistEntryRepository->expects($this->once())
            ->method('findByUserAndRace')
            ->with($userId, $raceId)
            ->willReturn(null);
        $this->watchlistEntryRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (WatchlistEntry $entry) use ($userId, $raceId): bool {
                return $entry->getUserId()->equals($userId) && $entry->getRaceId()->equals($raceId);
            }));

        $entryId = ($this->handler)(new AddWatchlistEntryCommand(
            $userId->toString(),
            $raceId->toString(),
        ));

        $this->assertInstanceOf(WatchlistEntryId::class, $entryId);
    }
}
