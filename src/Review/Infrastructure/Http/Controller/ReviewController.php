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
    public function list(#[CurrentUser] ?User $user, string $raceId, Request $request): JsonResponse
    {
        $race = RaceId::fromString($raceId);
        $page = max(1, $request->query->getInt('page', 1));
        $perPage = min(50, max(1, $request->query->getInt('perPage', 10)));
        $offset = ($page - 1) * $perPage;

        $userReview = null;
        if (null !== $user) {
            $userReview = $this->reviewRepository->findByUserAndRace($user->getId(), $race);
        }

        $reviews = $this->reviewRepository->findByRace($race, $perPage, $offset);
        $total = $this->reviewRepository->countByRace($race);

        $userIds = array_map(fn (Review $review) => $review->getUserId(), $reviews);
        $users = $this->userRepository->findByIds($userIds);

        return new JsonResponse([
            'reviews' => array_map(fn (Review $review) => [
                'id' => $review->getId()->toString(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'displayName' => ($users[$review->getUserId()->toString()] ?? null)?->getDisplayName() ?: 'Anonymous',
                'createdAt' => $review->getCreatedAt()->format('Y-m-d H:i:s'),
            ], $reviews),
            'userReview' => null !== $user && null !== $userReview ? [
                'id' => $userReview->getId()->toString(),
                'rating' => $userReview->getRating(),
                'comment' => $userReview->getComment(),
                'displayName' => $user->getDisplayName(),
                'createdAt' => $userReview->getCreatedAt()->format('Y-m-d H:i:s'),
            ] : null,
            'averageRating' => $this->reviewRepository->getAverageRating($race),
            'reviewCount' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
        ]);
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
