<?php

declare(strict_types=1);

namespace App\Review\Infrastructure\Http\Controller;

use App\RaceCatalog\Domain\Model\RaceId;
use App\Review\Domain\Model\Review;
use App\Review\Domain\Model\ReviewId;
use App\Review\Domain\Repository\ReviewRepositoryInterface;
use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    #[Route('/api/races/{raceId}/reviews', name: 'api_review_race_list', methods: ['GET'])]
    public function list(string $raceId): JsonResponse
    {
        $reviews = $this->reviewRepository->findByRace(RaceId::fromString($raceId));
        $userIds = array_map(fn (Review $review) => $review->getUserId(), $reviews);
        $users = $this->userRepository->findByIds($userIds);

        return new JsonResponse(array_map(fn (Review $r) => [
            'id' => $r->getId()->toString(),
            'rating' => $r->getRating(),
            'comment' => $r->getComment(),
            'displayName' => $users[$r->getUserId()->toString()]->getDisplayName() ?: 'Anonymous',
            'createdAt' => $r->getCreatedAt()->format('Y-m-d H:i:s'),
        ], $reviews));
    }

    #[Route('/api/races/{raceId}/reviews', name: 'api_review_race_add', methods: ['POST'])]
    public function add(#[CurrentUser] User $user, string $raceId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $rating = $data['rating'] ?? null;
        $comment = $data['comment'] ?? '';

        if (!\is_int($rating)) {
            return new JsonResponse(['error' => 'Rating is required and must be an integer.'], Response::HTTP_BAD_REQUEST);
        }

        // Check if the user has already reviewed this race
        $existingReview = $this->reviewRepository->findByUserAndRace($user->getId(), RaceId::fromString($raceId));
        if (null !== $existingReview) {
            return new JsonResponse(['error' => 'You have already reviewed this race.'], Response::HTTP_CONFLICT);
        }

        try {
            $review = Review::create(
                ReviewId::generate(),
                $user->getId(),
                RaceId::fromString($raceId),
                $rating,
                $comment,
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->reviewRepository->save($review);

        return new JsonResponse(['id' => $review->getId()->toString()], Response::HTTP_CREATED);
    }
}
