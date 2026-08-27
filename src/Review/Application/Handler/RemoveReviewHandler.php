<?php

declare(strict_types=1);

namespace App\Review\Application\Handler;

use App\Review\Application\Command\RemoveReviewCommand;
use App\Review\Domain\Event\ReviewRemoved;
use App\Review\Domain\Exception\ReviewNotExistsException;
use App\Review\Domain\Model\ReviewId;
use App\Review\Domain\Repository\ReviewRepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class RemoveReviewHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository,
        private MessageBusInterface $eventBus,
    ) {
    }

    public function __invoke(RemoveReviewCommand $command): void
    {
        $reviewId = ReviewId::fromString($command->reviewId);
        $review = $this->reviewRepository->findById($reviewId);
        if (null === $review) {
            throw new ReviewNotExistsException($reviewId->toString());
        }

        $raceId = $review->getRaceId();

        $this->reviewRepository->remove($review);
        $this->eventBus->dispatch(new ReviewRemoved($raceId));
    }
}
