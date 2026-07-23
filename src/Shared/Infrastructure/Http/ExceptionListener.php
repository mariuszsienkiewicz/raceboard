<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Review\Domain\Exception\RaceNotFoundException;
use App\Review\Domain\Exception\ReviewAlreadyExistsException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener()]
final class ExceptionListener
{
    private const EXCEPTION_MAP = [
        RaceNotFoundException::class => [404, 'race_not_found'],
        ReviewAlreadyExistsException::class => [409, 'review_already_exists'],
    ];

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        while (null !== $exception->getPrevious()) {
            $exception = $exception->getPrevious();
        }

        $mapping = self::EXCEPTION_MAP[$exception::class] ?? null;
        if (null === $mapping) {
            return;
        }
        [$status, $errorCode] = $mapping;

        $event->setResponse(new JsonResponse(
            ['error' => $errorCode],
            $status,
        ));
    }
}
