<?php

declare(strict_types=1);

namespace App\Notification\Application\Handler;

use App\Notification\Infrastructure\Email\WatchlistMailer;
use App\RaceCatalog\Domain\Event\RacesImported;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Shared\Domain\Model\UserId;
use App\UserProfile\Domain\Repository\UserRepositoryInterface;
use App\UserProfile\Domain\Repository\WatchlistEntryRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendWatchlistNotificationHandler
{
    public function __construct(
        private WatchlistEntryRepositoryInterface $watchlistRepository,
        private UserRepositoryInterface $userRepository,
        private RaceRepositoryInterface $raceRepository,
        private WatchlistMailer $mailer,
    ) {
    }

    public function __invoke(RacesImported $event): void
    {
        $races = $this->raceRepository->findByIds($event->raceIds);

        /** @var array<string, list<Race>> $userRaces */
        $userRaces = [];

        foreach ($races as $race) {
            foreach ($this->watchlistRepository->findUserIdsByCity($race->getCity()) as $userId) {
                $userRaces[$userId->toString()][] = $race;
            }
        }

        if ([] === $userRaces) {
            return;
        }

        $userIds = array_map(
            fn (string $id) => UserId::fromString($id),
            array_keys($userRaces),
        );
        $users = $this->userRepository->findByIds($userIds);

        foreach ($userRaces as $userIdString => $racesForUser) {
            $user = $users[$userIdString] ?? null;
            if (null !== $user) {
                $this->mailer->sendNewRacesDigest($user, $racesForUser);
            }
        }
    }
}
