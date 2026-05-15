<?php

declare(strict_types=1);

namespace App\UserProfile\Infrastructure\Http\Controller;

use App\RaceCatalog\Domain\Model\RaceId;
use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Model\WatchlistEntry;
use App\UserProfile\Domain\Model\WatchlistEntryId;
use App\UserProfile\Domain\Repository\WatchlistEntryRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class WatchlistController
{
    public function __construct(
        private WatchlistEntryRepositoryInterface $watchlistRepository,
    ) {
    }

    #[Route('/api/me/watchlist', name: 'api_watchlist_list', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        $entries = $this->watchlistRepository->findByUser($user->getId());

        return new JsonResponse(array_map(fn (WatchlistEntry $entry) => [
            'id' => $entry->getId()->toString(),
            'raceId' => $entry->getRaceId()->toString(),
            'createdAt' => $entry->getCreatedAt()->format('Y-m-d H:i:s'),
        ], $entries));
    }

    #[Route('/api/me/watchlist/{raceId}', name: 'api_watchlist_add', methods: ['POST'])]
    public function add(#[CurrentUser] User $user, string $raceId): JsonResponse
    {
        $existing = $this->watchlistRepository->findByUserAndRace(
            $user->getId(),
            RaceId::fromString($raceId),
        );

        if (null !== $existing) {
            return new JsonResponse(['error' => 'Race already in watchlist'], Response::HTTP_CONFLICT);
        }

        $entry = WatchlistEntry::create(
            WatchlistEntryId::generate(),
            $user->getId(),
            RaceId::fromString($raceId),
        );

        $this->watchlistRepository->save($entry);

        return new JsonResponse(['id' => $entry->getId()->toString()], Response::HTTP_CREATED);
    }

    #[Route('/api/me/watchlist/{raceId}', name: 'api_watchlist_remove', methods: ['DELETE'])]
    public function remove(#[CurrentUser] User $user, string $raceId): JsonResponse
    {
        $entry = $this->watchlistRepository->findByUserAndRace(
            $user->getId(),
            RaceId::fromString($raceId),
        );

        if (null === $entry) {
            return new JsonResponse(['error' => 'Race not in watchlist'], Response::HTTP_NOT_FOUND);
        }

        $this->watchlistRepository->remove($entry);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
