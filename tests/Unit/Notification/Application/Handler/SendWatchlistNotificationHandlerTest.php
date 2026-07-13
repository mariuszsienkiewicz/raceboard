<?php

declare(strict_types=1);

namespace App\Tests\Unit\Notification\Application\Handler;

use App\Notification\Application\Handler\SendWatchlistNotificationHandler;
use App\Notification\Infrastructure\Email\WatchlistMailer;
use App\RaceCatalog\Domain\Event\RacesImported;
use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Repository\UserRepositoryInterface;
use App\UserProfile\Domain\Repository\WatchlistEntryRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SendWatchlistNotificationHandlerTest extends TestCase
{
    private WatchlistEntryRepositoryInterface&MockObject $watchlistRepo;
    private UserRepositoryInterface&MockObject $userRepo;
    private WatchlistMailer&MockObject $mailer;
    private RaceRepositoryInterface&MockObject $raceRepo;
    private SendWatchlistNotificationHandler $handler;

    protected function setUp(): void
    {
        $this->watchlistRepo = $this->createMock(WatchlistEntryRepositoryInterface::class);
        $this->userRepo = $this->createMock(UserRepositoryInterface::class);
        $this->raceRepo = $this->createMock(RaceRepositoryInterface::class);
        $this->mailer = $this->createMock(WatchlistMailer::class);
        $this->handler = new SendWatchlistNotificationHandler(
            $this->watchlistRepo,
            $this->userRepo,
            $this->raceRepo,
            $this->mailer,
        );
    }

    public function testSendsOneDigestPerUserWithMultipleRaces(): void
    {
        $race1Id = RaceId::generate();
        $race2Id = RaceId::generate();
        $race1 = Race::create($race1Id, 'Maraton Warszawski', 'Warsaw', 'Masovian');
        $race2 = Race::create($race2Id, 'Półmaraton Warszawski', 'Warsaw', 'Masovian');

        $this->raceRepo->expects($this->once())
            ->method('findByIds')
            ->with([$race1Id, $race2Id])
            ->willReturn([$race1, $race2]);

        $userId = UserId::generate();
        $this->watchlistRepo->expects($this->exactly(2))
            ->method('findUserIdsByCity')
            ->with('Warsaw')
            ->willReturn([$userId]);

        $user = User::create($userId, 'anna@example.com', 'hash', 'Anna');
        $this->userRepo->expects($this->once())
            ->method('findByIds')
            ->with([$userId])
            ->willReturn([$userId->toString() => $user]);

        $this->mailer->expects($this->once())
            ->method('sendNewRacesDigest')
            ->with(
                $user,
                $this->callback(function (array $races) use ($race1, $race2): bool {
                    return 2 === count($races)
                        && in_array($race1, $races, true)
                        && in_array($race2, $races, true);
                }),
            );

        $this->handler->__invoke(new RacesImported([$race1Id, $race2Id]));
    }

    public function testSendsToMultipleUsersWatchingSameCity(): void
    {
        $raceId = RaceId::generate();
        $race = Race::create($raceId, 'Maraton Warszawski', 'Warsaw', 'Masovian');

        $this->raceRepo->expects($this->once())
            ->method('findByIds')
            ->with([$raceId])
            ->willReturn([$race]);

        $user1Id = UserId::generate();
        $user2Id = UserId::generate();
        $this->watchlistRepo->expects($this->once())
            ->method('findUserIdsByCity')
            ->with('Warsaw')
            ->willReturn([$user1Id, $user2Id]);

        $user1 = User::create($user1Id, 'anna@example.com', 'hash', 'Anna');
        $user2 = User::create($user2Id, 'bob@example.com', 'hash', 'Bob');
        $this->userRepo->expects($this->once())
            ->method('findByIds')
            ->willReturn([
                $user1Id->toString() => $user1,
                $user2Id->toString() => $user2,
            ]);

        $notifiedUsers = [];
        $this->mailer->expects($this->exactly(2))
            ->method('sendNewRacesDigest')
            ->willReturnCallback(function (User $user, array $races) use (&$notifiedUsers, $race): void {
                $notifiedUsers[] = $user;
                $this->assertSame([$race], $races);
            });

        $this->handler->__invoke(new RacesImported([$raceId]));

        $this->assertEqualsCanonicalizing([$user1, $user2], $notifiedUsers);
    }

    public function testDoesNothingWhenNoNewRaces(): void
    {
        $this->raceRepo->expects($this->once())
            ->method('findByIds')
            ->with([])
            ->willReturn([]);
        $this->watchlistRepo->expects($this->never())->method('findUserIdsByCity');
        $this->userRepo->expects($this->never())->method('findByIds');
        $this->mailer->expects($this->never())->method('sendNewRacesDigest');

        $this->handler->__invoke(new RacesImported([]));
    }
}
