<?php

declare(strict_types=1);

namespace App\Tests\Integration\DataImport\Infrastructure\Scraper;

use App\DataImport\Application\DateParser;
use App\DataImport\Infrastructure\Scraper\MaratonyPolskieAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class MaratonyPolskieAdapterTest extends TestCase
{
    private const string FIXTURES_DIR = __DIR__.'/Fixtures';

    private MaratonyPolskieAdapter $adapter;

    protected function setUp(): void
    {
        $html = file_get_contents(self::FIXTURES_DIR.'/maratony_polskie_calendar.html');
        $this->assertNotFalse($html);

        $mockClient = new MockHttpClient(new MockResponse($html));
        $dateParser = new DateParser();
        $this->adapter = new MaratonyPolskieAdapter($mockClient, $dateParser, new NullLogger(), 1, 0);
    }

    public function testParsesRaceNameCorrectly(): void
    {
        $results = $this->adapter->fetch();

        $this->assertSame('LENART XI Bieg Uliczny', $results[0]->name);
    }

    public function testParsesDateInCorrectFormat(): void
    {
        $results = $this->adapter->fetch();

        $this->assertSame('2026-05-01', $results[0]->date);
    }

    public function testParsesCityCorrectly(): void
    {
        $results = $this->adapter->fetch();

        $this->assertSame('Kępno', $results[0]->city);
    }

    public function testParsesSourceUrlWithFullPath(): void
    {
        $results = $this->adapter->fetch();

        $this->assertStringStartsWith('https://www.maratonypolskie.pl/', $results[0]->sourceUrl);
    }

    public function testReturnsCorrectNumberOfRaces(): void
    {
        $results = $this->adapter->fetch();

        $this->assertCount(191, $results);
    }
}
