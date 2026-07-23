<?php

declare(strict_types=1);

namespace Tests\Unit\Review\Application\Handler;

use App\Review\Application\Command\AddReviewCommand;
use App\Review\Application\Handler\AddReviewHandler;
use App\Review\Domain\Event\ReviewAdded;
use App\Review\Domain\Exception\RaceNotFoundException;
use App\Review\Domain\Exception\ReviewAlreadyExistsException;
use App\Review\Domain\Model\Review;
use App\Review\Domain\Model\ReviewId;
use App\Review\Domain\Repository\ReviewRepositoryInterface;
use App\Review\Domain\Service\RaceExistenceCheckerInterface;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class AddReviewHandlerTest extends TestCase
{
    private ReviewRepositoryInterface&MockObject $reviewRepository;
    private RaceExistenceCheckerInterface&MockObject $raceExistenceChecker;
    private MessageBusInterface&MockObject $eventBus;
    private AddReviewHandler $addReviewHandler;

    public function setUp(): void
    {
        $this->reviewRepository = $this->createMock(ReviewRepositoryInterface::class);
        $this->raceExistenceChecker = $this->createMock(RaceExistenceCheckerInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);
        $this->addReviewHandler = new AddReviewHandler(
            $this->reviewRepository,
            $this->raceExistenceChecker,
            $this->eventBus,
        );
    }

    public function testExceptionIsThrownIfRaceDoesNotExist(): void
    {
        $this->expectException(RaceNotFoundException::class);

        $this->raceExistenceChecker->expects($this->once())
            ->method('exists')
            ->willReturn(false);
        $this->reviewRepository->expects($this->never())
            ->method('save');
        $this->eventBus->expects($this->never())
            ->method('dispatch');

        $this->addReviewHandler->__invoke(new AddReviewCommand(
            RaceId::generate()->toString(),
            UserId::generate()->toString(),
            5,
            'Test review',
        ));
    }

    public function testExceptionIsThrownIfReviewAlreadyExists(): void
    {
        $this->expectException(ReviewAlreadyExistsException::class);

        $userId = UserId::generate();
        $raceId = RaceId::generate();

        $this->raceExistenceChecker->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $this->reviewRepository->expects($this->once())
            ->method('findByUserAndRace')
            ->willReturn(Review::create(ReviewId::generate(), $userId, $raceId, 4, 'Test review'));
        $this->reviewRepository->expects($this->never())
            ->method('save');
        $this->eventBus->expects($this->never())
            ->method('dispatch');

        $this->addReviewHandler->__invoke(new AddReviewCommand(
            $raceId->toString(),
            $userId->toString(),
            5,
            'Test review 2',
        ));
    }

    public function testReviewIsSavedAndEventIsDispatched(): void
    {
        $userId = UserId::generate();
        $raceId = RaceId::generate();

        $this->raceExistenceChecker->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->reviewRepository->expects($this->once())
            ->method('findByUserAndRace')
            ->willReturn(null);
        $this->reviewRepository->expects($this->once())
            ->method('save');
        $this->eventBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (ReviewAdded $event) use ($raceId): bool {
                return $event->raceId->equals($raceId);
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $this->addReviewHandler->__invoke(new AddReviewCommand(
            $raceId->toString(),
            $userId->toString(),
            5,
            'Test review',
        ));
    }
}
