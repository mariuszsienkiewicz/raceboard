<?php

declare(strict_types=1);

namespace App\Search\Infrastructure\Cache;

use App\RaceCatalog\Domain\Model\Race;
use App\Search\Domain\SearchIndexInterface;
use App\Search\Domain\SearchQuery;
use App\Search\Domain\SearchResult;
use App\Search\Infrastructure\MeiliSearch\MeiliSearchAdapter;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AsDecorator(decorates: MeiliSearchAdapter::class)]
final readonly class CachedSearchIndex implements SearchIndexInterface
{
    private const string CACHE_TAG = 'search';
    private const string KEY_PREFIX = 'search.result.';

    public function __construct(
        #[AutowireDecorated]
        private SearchIndexInterface $inner,
        private TagAwareCacheInterface $cache,
        private LoggerInterface $logger,
        #[Autowire('%env(int:SEARCH_CACHE_TTL)%')]
        private int $ttlSeconds,
    ) {
    }

    public function configureIndex(): void
    {
        $this->inner->configureIndex();
    }

    public function indexRace(Race $race): void
    {
        $this->inner->indexRace($race);
        $this->invalidateSearchCache();
    }

    public function indexRaces(array $races): void
    {
        $this->inner->indexRaces($races);
        $this->invalidateSearchCache();
    }

    public function indexAll(array $races): void
    {
        $this->inner->indexAll($races);
        $this->invalidateSearchCache();
    }

    public function search(SearchQuery $query): SearchResult
    {
        try {
            /** @var array{hits: array<int, mixed>, totalHits: int|null, page: int, perPage: int, totalPages: int} $payload */
            $payload = $this->cache->get(
                $this->buildKey($query),
                function (ItemInterface $item) use ($query) {
                    $item->tag(self::CACHE_TAG);
                    $item->expiresAfter($this->ttlSeconds);

                    return $this->toPayload($this->inner->search($query));
                },
            );

            return $this->fromPayload($payload);
        } catch (\Throwable $e) {
            // Redis down shouldn't affect the search
            $this->logger->warning('Search cache unavailable, falling back to MeiliSearch', [
                'exception' => $e,
            ]);

            return $this->inner->search($query);
        }
    }

    /**
     * Will not be cached, low possibility of hitting the cache (bbox pan / zoom change).
     */
    public function searchMapPoints(SearchQuery $query): array
    {
        return $this->inner->searchMapPoints($query);
    }

    private function invalidateSearchCache(): void
    {
        try {
            $this->cache->invalidateTags([self::CACHE_TAG]);
        } catch (\Throwable $e) {
            $this->logger->warning('Search cache invalidation failed', [
                'exception' => $e,
            ]);
        }
    }

    private function buildKey(SearchQuery $query): string
    {
        $voivodeships = $query->voivodeships;
        $distances = $query->distancesKm;
        sort($voivodeships);
        sort($distances);

        return self::KEY_PREFIX.\hash('xxh128', \json_encode([
            $query->query,
            $query->city,
            $voivodeships,
            $distances,
            $query->dateFrom,
            $query->dateTo,
            $query->page,
            $query->perPage,
        ], \JSON_THROW_ON_ERROR));
    }

    /**
     * @return array{hits: array<int, mixed>, totalHits: int|null, page: int, perPage: int, totalPages: int}
     */
    private function toPayload(SearchResult $result): array
    {
        return [
            'hits' => $result->hits,
            'totalHits' => $result->totalHits,
            'page' => $result->page,
            'perPage' => $result->perPage,
            'totalPages' => $result->totalPages,
        ];
    }

    /**
     * @param array{hits: array<int, mixed>, totalHits: int|null, page: int, perPage: int, totalPages: int} $payload
     */
    private function fromPayload(array $payload): SearchResult
    {
        return new SearchResult(
            $payload['hits'],
            $payload['totalHits'],
            $payload['page'],
            $payload['perPage'],
            $payload['totalPages'],
        );
    }
}
