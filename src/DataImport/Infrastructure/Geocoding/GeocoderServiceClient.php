<?php

declare(strict_types=1);

namespace App\DataImport\Infrastructure\Geocoding;

use App\DataImport\Domain\Geocoding\GeocoderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocoderServiceClient implements GeocoderInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $geocoderUrl,
    ) {
    }

    public function geocode(string $city): ?array
    {
        try {
            $response = $this->httpClient->request('POST', $this->geocoderUrl.'/geocode', [
                'json' => ['cities' => [$city]],
                'timeout' => 15,
            ]);

            $data = $response->toArray();

            if (empty($data['coordinates'])) {
                return null;
            }

            $result = $data['coordinates'][0];

            return ['lat' => (float) $result['lat'], 'lng' => (float) $result['lng']];
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('Geocoder service failed for %s: %s', $city, $e->getMessage()));

            return null;
        }
    }

    public function geocodeMany(array $cities): array
    {
        if ([] === $cities) {
            return [];
        }

        try {
            $response = $this->httpClient->request('POST', $this->geocoderUrl.'/geocode', [
                'json' => ['cities' => $cities],
                'timeout' => 120, // batch will take longer (1 req/s to Nominatim)
            ]);

            $data = $response->toArray();

            $result = [];
            foreach ($data['coordinates'] ?? [] as $entry) {
                $result[$entry['city']] = [
                    'lat' => (float) $entry['lat'],
                    'lng' => (float) $entry['lng'],
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('Geocoder service batch failed: %s', $e->getMessage()));

            return [];
        }
    }
}
