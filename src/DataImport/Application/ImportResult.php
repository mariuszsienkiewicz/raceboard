<?php

declare(strict_types=1);

namespace App\DataImport\Application;

class ImportResult
{
    /**
     * @param array<string> $errors
     */
    public function __construct(
        public int $importedCount = 0,
        public int $updatedCount = 0,
        public int $skippedCount = 0,
        public array $errors = [],
    ) {
    }

    public function incrementImported(): void
    {
        ++$this->importedCount;
    }

    public function incrementUpdated(): void
    {
        ++$this->updatedCount;
    }

    public function incrementSkipped(): void
    {
        ++$this->skippedCount;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }
}
