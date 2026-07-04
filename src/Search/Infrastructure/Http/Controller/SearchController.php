<?php

declare(strict_types=1);

namespace App\Search\Infrastructure\Http\Controller;

use App\Search\Domain\SearchIndexInterface;
use App\Search\Domain\SearchQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SearchController
{
    public function __construct(
        private SearchIndexInterface $searchIndex,
    ) {
    }

    #[Route('/api/search', name: 'api_races_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = new SearchQuery(
            query: $request->query->getString('q', ''),
            city: $request->query->get('city'),
            voivodeship: $request->query->get('voivodeship'),
            distanceKm: $request->query->has('distance') ? (float) $request->query->get('distance') : null,
            dateFrom: $request->query->get('dateFrom'),
            dateTo: $request->query->get('dateTo'),
            page: $request->query->getInt('page', 1),
            perPage: $request->query->getInt('perPage', 20),
        );

        $result = $this->searchIndex->search($query);

        return new JsonResponse([
            'hits' => $result->hits,
            'totalHits' => $result->totalHits,
            'page' => $result->page,
            'perPage' => $result->perPage,
            'totalPages' => $result->totalPages,
        ]);
    }

    #[Route('/api/search/map', name: 'api_races_search_map', methods: ['GET'])]
    public function searchMap(Request $request): JsonResponse
    {
        $query = new SearchQuery(
            query: $request->query->getString('q', ''),
            city: $request->query->get('city'),
            voivodeship: $request->query->get('voivodeship'),
            distanceKm: $request->query->has('distance') ? (float) $request->query->get('distance') : null,
            dateFrom: $request->query->get('dateFrom'),
            dateTo: $request->query->get('dateTo'),
            topLat: $this->parseOptionalFloat($request, 'topLat'),
            topLng: $this->parseOptionalFloat($request, 'topLng'),
            bottomLat: $this->parseOptionalFloat($request, 'bottomLat'),
            bottomLng: $this->parseOptionalFloat($request, 'bottomLng'),
        );

        return new JsonResponse($this->searchIndex->searchMapPoints($query));
    }

    private function parseOptionalFloat(Request $request, string $key): ?float
    {
        if (!$request->query->has($key)) {
            return null;
        }

        $value = $request->query->get($key);

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
