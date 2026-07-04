<?php

declare(strict_types=1);

namespace App\DataImport\Infrastructure\Geocoding;

use App\DataImport\Domain\Geocoding\GeocoderInterface;
use App\Shared\Domain\CityCoordinates;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NominatimGeocoder implements GeocoderInterface
{
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

        return $this->cache->get('geo_'.$cityKey, function () use ($city) {
            return $this->fetchFromNominatim($city);
        });
    }

    /** @return array{lat: float, lng: float}|null */
    private function fetchFromNominatim(string $city): ?array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://nominatim.openstreetmap.org/search', [
                'query' => ['q' => $city.', Poland', 'format' => 'json', 'limit' => 1],
                'headers' => ['User-Agent' => 'RaceBoard/1.0 (contact@heaps.pl)'],
            ]);

            $data = $response->toArray();
            sleep(1);

            if ([] === $data) {
                $this->logger->info(sprintf('No geocoding result for city: %s', $city));

                return null;
            }

            return ['lat' => (float) $data[0]['lat'], 'lng' => (float) $data[0]['lon']];
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('Geocoding failed for %s: %s', $city, $e->getMessage()));

            return null;
        }
    }
}
