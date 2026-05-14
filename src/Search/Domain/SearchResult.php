<?php

declare(strict_types=1);

namespace App\Search\Domain;

final readonly class SearchResult
{
    /**
     * @param array<int, mixed> $hits
     */
    public function __construct(
        public array $hits,
        public ?int $totalHits,
        public int $page,
        public int $perPage,
    ) {
    }
}
