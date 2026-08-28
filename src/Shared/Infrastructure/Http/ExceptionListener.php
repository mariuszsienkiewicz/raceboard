<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Review\Domain\Exception\RaceNotFoundException as ReviewRaceNotFoundException;
use App\Review\Domain\Exception\ReviewAlreadyExistsException;
use App\Review\Domain\Exception\ReviewNotExistsException;
use App\UserProfile\Domain\Exception\RaceNotFoundException as UserProfileRaceNotFoundException;
use App\UserProfile\Domain\Exception\WatchlistEntryAlreadyExistsException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener()]
final class ExceptionListener
{
    private const EXCEPTION_MAP = [
        ReviewRaceNotFoundException::class => [404, 'race_not_found'],
        ReviewAlreadyExistsException::class => [409, 'review_already_exists'],
        ReviewNotExistsException::class => [404, 'review_not_exists'],
        WatchlistEntryAlreadyExistsException::class => [409, 'watchlist_entry_already_exists'],
        UserProfileRaceNotFoundException::class => [404, 'race_not_found'],
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
