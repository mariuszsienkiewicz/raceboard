<?php

declare(strict_types=1);

namespace App\DataImport\Domain;

interface ImportAdapterInterface
{
    /** @return list<RawRaceData> */
    public function import(): array;
}
