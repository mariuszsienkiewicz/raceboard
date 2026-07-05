<?php

declare(strict_types=1);

namespace App\DataImport\Application\Message;

use App\DataImport\Application\Handler\GeocodeRacesHandler;
use App\RaceCatalog\Domain\Event\RacesImported;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GeocodeRacesOnImportHandler
{
    public function __construct(
        private GeocodeRacesHandler $handler,
    ) {
    }

    public function __invoke(RacesImported $event): void
    {
        $this->handler->handle($event->raceIds);
    }
}
