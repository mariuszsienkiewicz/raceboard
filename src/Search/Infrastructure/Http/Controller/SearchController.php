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
    ) {}

    #[Route('/api/races/search', name: 'api_races_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = new SearchQuery(
            $request->query->getString('q', ''),
            $request->query->get('city'),
            $request->query->get('voivodeship'),
            $request->query->has('distance') ? (float) $request->query->get('distance') : null,
            null,
            null,
            $request->query->getInt('page', 1),
            $request->query->getInt('perPage', 20),
        );

        $result = $this->searchIndex->search($query);

        return new JsonResponse([
            'hits' => $result->hits,
            'totalHits' => $result->totalHits,
            'page' => $result->page,
            'perPage' => $result->perPage,
        ]);
    }
}
