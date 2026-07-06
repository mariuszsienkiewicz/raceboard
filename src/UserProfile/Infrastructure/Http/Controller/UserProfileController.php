<?php

declare(strict_types=1);

namespace App\UserProfile\Infrastructure\Http\Controller;

use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserProfileController
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    #[Route('/api/me', name: 'api_user_profile_show', methods: ['GET'])]
    public function show(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse([
            'email' => $user->getEmail(),
            'displayName' => $user->getDisplayName(),
        ]);
    }

    #[Route('/api/me', name: 'api_user_profile_update', methods: ['PATCH'])]
    public function update(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['displayName'])) {
            try {
                $user->changeDisplayName($data['displayName']);
            } catch (\InvalidArgumentException $e) {
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }
        }

        $this->userRepository->save($user);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
