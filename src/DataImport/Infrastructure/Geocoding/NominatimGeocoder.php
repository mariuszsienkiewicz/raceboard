<?php

declare(strict_types=1);

namespace App\DataImport\Infrastructure\Geocoding;

use App\DataImport\Domain\Geocoding\GeocoderInterface;
use App\Shared\Domain\CityCoordinates;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NominatimGeocoder implements GeocoderInterface
{
    private const MAX_RETRIES = 3;

    // Nominatim usage policy allows at most 1 request per second.
    private const float MIN_INTERVAL_SECONDS = 1.0;

    private float $lastRequestAt = 0.0;

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private LoggerInterface $logger,
    ) {
    }

    public function geocode(string $city): ?array
    {
        $static = CityCoordinates::get($city);
        if (null !== $static) {
            return $static;
        }

        $cityKey = md5(mb_strtolower(trim($city)));

        // A transient failure (429/network) throws out of the callback, so the
        // cache stores nothing and the city is retried on the next run.
        // A genuine "not found" returns null and is cached.
        return $this->cache->get('geo_'.$cityKey, function (ItemInterface $item) use ($city) {
            $result = $this->fetchFromNominatim($city);
            $item->expiresAfter(null === $result ? 60 * 60 * 24 * 7 : 60 * 60 * 24 * 30);

            return $result;
        });
    }

    /** @return array{lat: float, lng: float}|null */
    private function fetchFromNominatim(string $city): ?array
    {
        for ($attempt = 1; $attempt <= self::MAX_RETRIES; ++$attempt) {
            $this->throttle();

            $response = $this->httpClient->request('GET', 'https://nominatim.openstreetmap.org/search', [
                'query' => ['q' => $city.', Poland', 'format' => 'json', 'limit' => 1],
                'headers' => ['User-Agent' => 'RaceBoard/1.0 (contact@heaps.pl)'],
            ]);

            $status = $response->getStatusCode();

            if (429 === $status) {
                $wait = $attempt * 2; // linear backoff: 2s, 4s, 6s
                $this->logger->warning(sprintf('Rate limited on %s, retry %d/%d after %ds', $city, $attempt, self::MAX_RETRIES, $wait));
                sleep($wait);
                continue;
            }

            $data = $response->toArray();

            if ([] === $data) {
                $this->logger->info(sprintf('No geocoding result for city: %s', $city));

                return null; // genuine "not found", safe to cache
            }

            return ['lat' => (float) $data[0]['lat'], 'lng' => (float) $data[0]['lon']];
        }

        // Exhausted retries on 429 transient error, so throw to avoid caching a null.
        throw new \RuntimeException(sprintf('Geocoding rate-limited after %d retries for %s', self::MAX_RETRIES, $city));
    }

    private function throttle(): void
    {
        $elapsed = microtime(true) - $this->lastRequestAt;
        if ($elapsed < self::MIN_INTERVAL_SECONDS) {
            usleep((int) ((self::MIN_INTERVAL_SECONDS - $elapsed) * 1_000_000));
        }

        $this->lastRequestAt = microtime(true);
    }
}
