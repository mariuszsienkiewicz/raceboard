<?php

declare(strict_types=1);

namespace App\Tests\Integration\DataImport\Infrastructure\Scraper;

use App\DataImport\Application\DateParser;
use App\DataImport\Application\Normalizer\DistanceNormalizer;
use App\DataImport\Application\Normalizer\VoivodeshipNormalizer;
use App\DataImport\Infrastructure\Scraper\RunningLifeAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class RunningLifeAdapterTest extends TestCase
{
    private const string FIXTURES_DIR = __DIR__.'/Fixtures';

    private RunningLifeAdapter $adapter;

    protected function setUp(): void
    {
        $html = file_get_contents(self::FIXTURES_DIR.'/running_life_calendar.html');
        $this->assertNotFalse($html);

        $mockClient = new MockHttpClient(new MockResponse($html));
        $voivodeshipNormalizer = new VoivodeshipNormalizer();
        $distanceNormalizer = new DistanceNormalizer();
        $dateParser = new DateParser();
        $this->adapter = new RunningLifeAdapter($mockClient, $voivodeshipNormalizer, $distanceNormalizer, $dateParser);
    }

    public function testParsesRaceNameCorrectly(): void
    {
        $results = $this->adapter->fetch();

        $this->assertSame('Rajd Beskidy short', $results[0]->name);
    }

    public function testParsesDateInCorrectFormat(): void
    {
        $results = $this->adapter->fetch();

        $this->assertSame('2026-05-07', $results[0]->date);
    }

    public function testParsesCityCorrectly(): void
    {
        $results = $this->adapter->fetch();

        $this->assertSame('Koszyce', $results[0]->city);
    }

    public function testParsesVoivodeshipCorrectly(): void
    {
        $results = $this->adapter->fetch();

        $this->assertSame('małopolskie', $results[0]->voivodeship);
    }

    public function testParsesSourceUrlWithFullPath(): void
    {
        $results = $this->adapter->fetch();

        $this->assertStringStartsWith('https://running.life/', $results[0]->sourceUrl);
    }

    public function testReturnsCorrectNumberOfRaces(): void
    {
        $results = $this->adapter->fetch();

        $this->assertCount(20, $results);
    }
}
