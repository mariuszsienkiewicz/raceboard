<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataImport\Infrastructure\Geocoding;

use App\DataImport\Infrastructure\Geocoding\GeocoderServiceClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GeocoderServiceClientTest extends TestCase
{
    private MockHttpClient $httpClientMock;
    private GeocoderServiceClient $geocoderServiceClient;

    protected function setUp(): void
    {
        $this->httpClientMock = new MockHttpClient();
        $this->geocoderServiceClient = new GeocoderServiceClient(
            $this->httpClientMock,
            new NullLogger(),
            'http://localhost:12345',
        );
    }

    public function testGeocodeReturnsCorrectCoordinates(): void
    {
        $this->httpClientMock->setResponseFactory(new MockResponse('{"coordinates":[{"city":"Warszawa","lat":52.2333742,"lng":21.0711489}]}'));
        $result = $this->geocoderServiceClient->geocode('Warszawa');
        $this->assertSame(['lat' => 52.2333742, 'lng' => 21.0711489], $result);
    }

    public function testGeocodeReturnNullWhenCityNotFound(): void
    {
        $this->httpClientMock->setResponseFactory(new MockResponse('{"coordinates":[]}'));
        $result = $this->geocoderServiceClient->geocode('Warszawa');
        $this->assertNull($result);
    }

    public function testGeocodeManyReturnsCorrectCoordinates(): void
    {
        $this->httpClientMock->setResponseFactory(new MockResponse('{"coordinates":[{"city":"Warszawa","lat":52.2333742,"lng":21.0711489},{"city":"Krakow","lat":50.0646500,"lng":19.9449800}]}'));
        $result = $this->geocoderServiceClient->geocodeMany(['Warszawa', 'Krakow']);
        $this->assertSame([
            'Warszawa' => ['lat' => 52.2333742, 'lng' => 21.0711489],
            'Krakow' => ['lat' => 50.0646500, 'lng' => 19.9449800],
        ], $result);
    }

    public function testGeocodeManyReturnsEmptyArrayWhenNoCitiesProvided(): void
    {
        $result = $this->geocoderServiceClient->geocodeMany([]);
        $this->assertEquals([], $result);
    }

    public function testGeocodeReturnsNullOnServiceError(): void
    {
        $this->httpClientMock->setResponseFactory(new MockResponse('', ['http_code' => 500]));
        $result = $this->geocoderServiceClient->geocode('Warszawa');
        $this->assertNull($result);
    }

    public function testGeocodeManyReturnsEmptyArrayOnServiceError(): void
    {
        $this->httpClientMock->setResponseFactory(new MockResponse('', ['http_code' => 500]));
        $result = $this->geocoderServiceClient->geocodeMany(['Warszawa']);
        $this->assertEquals([], $result);
    }
}
