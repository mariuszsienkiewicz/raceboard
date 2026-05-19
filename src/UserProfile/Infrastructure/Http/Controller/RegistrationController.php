<?php

declare(strict_types=1);

namespace App\UserProfile\Infrastructure\Http\Controller;

use App\UserProfile\Application\RegisterUserHandler;
use App\UserProfile\Domain\Exception\EmailAlreadyExistsException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController
{
    public function __construct(
        private RegisterUserHandler $handler,
    ) {
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? '';
        $plainPassword = $data['password'] ?? '';
        $displayName = $data['displayName'] ?? '';

        if ('' === $email || '' === $plainPassword || '' === $displayName) {
            return new JsonResponse(['error' => 'Email, password, and name are required.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->handler->handle($email, $plainPassword, $displayName);

            return new JsonResponse(['id' => $user->getIdString(), 'email' => $user->getEmail()], Response::HTTP_CREATED);
        } catch (EmailAlreadyExistsException) {
            return new JsonResponse(['error' => 'Email already exists.'], Response::HTTP_CONFLICT);
        }
    }
}
