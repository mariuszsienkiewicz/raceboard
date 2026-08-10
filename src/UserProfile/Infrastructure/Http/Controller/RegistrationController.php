<?php

declare(strict_types=1);

namespace App\UserProfile\Infrastructure\Http\Controller;

use App\UserProfile\Application\RegisterUserHandler;
use App\UserProfile\Domain\Exception\EmailAlreadyExistsException;
use App\UserProfile\Infrastructure\Http\Request\RegisterUserRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController
{
    public function __construct(
        private RegisterUserHandler $handler,
    ) {
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(#[MapRequestPayload] RegisterUserRequest $registerRequest): JsonResponse
    {
        try {
            $user = $this->handler->handle($registerRequest->email, $registerRequest->password, $registerRequest->displayName);

            return new JsonResponse(['id' => $user->getIdString(), 'email' => $user->getEmail()], Response::HTTP_CREATED);
        } catch (EmailAlreadyExistsException) {
            return new JsonResponse(['error' => 'Email already exists.'], Response::HTTP_CONFLICT);
        }
    }
}
