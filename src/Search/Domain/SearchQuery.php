<?php

declare(strict_types=1);

namespace App\Search\Domain;

final readonly class SearchQuery
{
    public function __construct(
        public string $query = '',
        public ?string $city = null,
        public ?string $voivodeship = null,
        public ?float $distanceKm = null,
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
