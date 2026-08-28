<?php

declare(strict_types=1);

namespace App\UserProfile\Application;

use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use App\UserProfile\Domain\Exception\RaceNotFoundException;
use App\UserProfile\Domain\Exception\WatchlistEntryAlreadyExistsException;
use App\UserProfile\Domain\Model\WatchlistEntry;
use App\UserProfile\Domain\Model\WatchlistEntryId;
use App\UserProfile\Domain\Repository\WatchlistEntryRepositoryInterface;
use App\UserProfile\Domain\Service\RaceExistenceCheckerInterface;

final readonly class AddWatchlistEntryHandler
{
    public function __construct(
        private WatchlistEntryRepositoryInterface $watchlistEntryRepository,
        private RaceExistenceCheckerInterface $raceExistenceChecker,
    ) {
    }

    public function __invoke(AddWatchlistEntryCommand $command): WatchlistEntryId
    {
        $raceId = RaceId::fromString($command->raceId);
        $userId = UserId::fromString($command->userId);

        if (!$this->raceExistenceChecker->exists($raceId)) {
            throw RaceNotFoundException::forRace($raceId->toString());
        }

        if (null !== $this->watchlistEntryRepository->findByUserAndRace($userId, $raceId)) {
            throw WatchlistEntryAlreadyExistsException::forRace($raceId->toString());
        }

        $entry = WatchlistEntry::create(
            WatchlistEntryId::generate(),
            $userId,
            $raceId,
        );

        $this->watchlistEntryRepository->save($entry);

        return $entry->getId();
    }
}
