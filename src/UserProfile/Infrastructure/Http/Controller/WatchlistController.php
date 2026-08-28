<?php

declare(strict_types=1);

namespace App\UserProfile\Infrastructure\Http\Controller;

use App\RaceCatalog\Domain\Model\Distance;
use App\RaceCatalog\Domain\Model\Edition;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use App\UserProfile\Application\AddWatchlistEntryCommand;
use App\UserProfile\Application\AddWatchlistEntryHandler;
use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Model\WatchlistEntry;
use App\UserProfile\Domain\Repository\WatchlistEntryRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class WatchlistController
{
    public function __construct(
        private WatchlistEntryRepositoryInterface $watchlistRepository,
        private RaceRepositoryInterface $raceRepository,
    ) {
    }

    #[Route('/api/me/watchlist', name: 'api_watchlist_list', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        $entries = $this->watchlistRepository->findByUser($user->getId());
        $raceIds = array_map(fn (WatchlistEntry $entry) => $entry->getRaceId(), $entries);
        $races = $this->raceRepository->findByIds($raceIds);

        return new JsonResponse(array_map(function (WatchlistEntry $entry) use ($races) {
            $race = $races[$entry->getRaceId()->toString()] ?? null;

            return [
                'id' => $entry->getId()->toString(),
                'race' => $race ? [
                    'id' => $race->getId()->toString(),
                    'name' => $race->getName(),
                    'city' => $race->getCity(),
                    'slug' => $race->getSlug(),
                    'voivodeship' => $race->getVoivodeship(),
                    'country' => $race->getCountry(),
                    'editions' => array_map(fn (Edition $edition) => [
                        'date' => $edition->getDate()->format('Y-m-d'),
                        'distances' => array_map(fn (Distance $distance) => [
                            'id' => $distance->getId()->toString(),
                            'name' => $distance->getName(),
                            'lengthInKm' => $distance->getLengthInKm(),
                        ], $edition->getDistances()),
                    ], $race->getEditions()),
                ] : null,
                'createdAt' => $entry->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $entries));
    }

    #[Route('/api/me/watchlist/check', name: 'api_watchlist_check_batch', methods: ['POST'])]
    public function checkBatch(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $rawRaceIds = \is_array($data) ? ($data['raceIds'] ?? []) : [];

        if (!\is_array($rawRaceIds) || [] === $rawRaceIds) {
            return new JsonResponse(['watchedRaceIds' => []]);
        }

        $raceIds = array_map(
            static fn (mixed $raceId): RaceId => RaceId::fromString((string) $raceId),
            array_values($rawRaceIds),
        );

        $watchedRaceIds = $this->watchlistRepository->findWatchedRaceIds($user->getId(), $raceIds);

        return new JsonResponse([
            'watchedRaceIds' => array_map(
                static fn (RaceId $raceId): string => $raceId->toString(),
                $watchedRaceIds,
            ),
        ]);
    }

    #[Route('/api/me/watchlist/{raceId}/check', name: 'api_watchlist_check', methods: ['GET'])]
    public function check(#[CurrentUser] User $user, string $raceId): JsonResponse
    {
        $entry = $this->watchlistRepository->findByUserAndRace(
            $user->getId(),
            RaceId::fromString($raceId),
        );

        return new JsonResponse(['watched' => null !== $entry]);
    }

    #[Route('/api/me/watchlist/{raceId}', name: 'api_watchlist_add', methods: ['POST'])]
    public function add(#[CurrentUser] User $user, string $raceId, AddWatchlistEntryHandler $handler): JsonResponse
    {
        $entryId = $handler(new AddWatchlistEntryCommand(
            $user->getId()->toString(),
            $raceId,
        ));

        return new JsonResponse(['id' => $entryId->toString()], Response::HTTP_CREATED);
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
