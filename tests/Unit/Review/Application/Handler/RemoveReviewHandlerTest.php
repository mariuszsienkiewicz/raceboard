<?php

declare(strict_types=1);

namespace App\Tests\Unit\Review\Application\Handler;

use App\Review\Application\Command\RemoveReviewCommand;
use App\Review\Application\Handler\RemoveReviewHandler;
use App\Review\Domain\Event\ReviewRemoved;
use App\Review\Domain\Exception\ReviewNotExistsException;
use App\Review\Domain\Model\Review;
use App\Review\Domain\Model\ReviewId;
use App\Review\Domain\Repository\ReviewRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class RemoveReviewHandlerTest extends TestCase
{
    private ReviewRepositoryInterface&MockObject $reviewRepository;
    private MessageBusInterface&MockObject $eventBus;
    private RemoveReviewHandler $removeReviewHandler;

    protected function setUp(): void
    {
        $this->reviewRepository = $this->createMock(ReviewRepositoryInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);
        $this->removeReviewHandler = new RemoveReviewHandler($this->reviewRepository, $this->eventBus);
    }

    public function testExceptionIsThrownIfReviewDoesNotExist(): void
    {
        $this->expectException(ReviewNotExistsException::class);

        $this->reviewRepository->expects($this->once())
            ->method('findById')
            ->willReturn(null);
        $this->reviewRepository->expects($this->never())
            ->method('remove');
        $this->eventBus->expects($this->never())
            ->method('dispatch');

        $this->removeReviewHandler->__invoke(new RemoveReviewCommand(
            ReviewId::generate()->toString(),
        ));
    }

    public function testReviewIsRemovedAndEventIsDispatched(): void
    {
        $reviewId = ReviewId::generate();
        $raceId = RaceId::generate();
        $userId = UserId::generate();
        $review = Review::create($reviewId, $userId, $raceId, 5, 'Test review');

        $this->reviewRepository->expects($this->once())
            ->method('findById')
            ->with($reviewId)
            ->willReturn($review);
        $this->reviewRepository->expects($this->once())
            ->method('remove')
            ->with($review);
        $this->eventBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (ReviewRemoved $event) use ($raceId): bool {
                return $event->raceId->equals($raceId);
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $this->removeReviewHandler->__invoke(new RemoveReviewCommand(
            $reviewId->toString(),
        ));
    }
}
