<?php

declare(strict_types=1);

namespace App\Search\Domain;

final readonly class SearchQuery
{
    /**
     * @param array<string> $voivodeships
     * @param array<string> $distancesKm
     */
    public function __construct(
        public string $query = '',
        public ?string $city = null,
        public array $voivodeships = [],
        public array $distancesKm = [],
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?float $topLat = null,
        public ?float $topLng = null,
        public ?float $bottomLat = null,
        public ?float $bottomLng = null,
        public int $page = 1,
        public int $perPage = 20,
    ) {
    }
}
