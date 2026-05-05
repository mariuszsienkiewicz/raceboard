<?php

declare(strict_types=1);

namespace App\DataImport\Domain;

interface ImportAdapterInterface
{
    public function getName(): string;

    /** @return list<RawRaceData> */
    public function fetch(): array;
}
