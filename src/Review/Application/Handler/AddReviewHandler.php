<?php

declare(strict_types=1);

namespace App\Review\Application\Handler;

use App\Review\Application\Command\AddReviewCommand;
use App\Review\Domain\Event\ReviewAdded;
use App\Review\Domain\Exception\RaceNotFoundException;
use App\Review\Domain\Exception\ReviewAlreadyExistsException;
use App\Review\Domain\Model\Review;
use App\Review\Domain\Model\ReviewId;
use App\Review\Domain\Repository\ReviewRepositoryInterface;
use App\Review\Domain\Service\RaceExistenceCheckerInterface;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class AddReviewHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository,
        private RaceExistenceCheckerInterface $raceExistenceChecker,
        private MessageBusInterface $eventBus,
    ) {
    }

    public function __invoke(AddReviewCommand $command): void
    {
        $raceId = RaceId::fromString($command->raceId);
        if (!$this->raceExistenceChecker->exists($raceId)) {
            throw new RaceNotFoundException($raceId->toString());
        }

        $userId = UserId::fromString($command->userId);
        if ($this->reviewRepository->findByUserAndRace($userId, $raceId)) {
            throw new ReviewAlreadyExistsException($raceId->toString());
        }

        $review = Review::create(
            ReviewId::generate(),
            $userId,
            $raceId,
            $command->rating,
            $command->comment,
        );

        $this->reviewRepository->save($review);
        $this->eventBus->dispatch(new ReviewAdded($raceId));
    }
}
