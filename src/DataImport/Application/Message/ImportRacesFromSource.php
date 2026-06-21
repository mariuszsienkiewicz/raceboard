<?php

declare(strict_types=1);

namespace App\DataImport\Application\Message;

final readonly class ImportRacesFromSource
{
    public function __construct(
        public string $sourceName,
    ) {}
}
