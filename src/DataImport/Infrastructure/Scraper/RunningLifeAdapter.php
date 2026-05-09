<?php

declare(strict_types=1);

namespace App\DataImport\Infrastructure\Scraper;

use App\DataImport\Application\Normalizer\DistanceNormalizer;
use App\DataImport\Application\Normalizer\VoivodeshipNormalizer;
use App\DataImport\Domain\ImportAdapterInterface;
use App\DataImport\Domain\RawRaceData;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RunningLifeAdapter implements ImportAdapterInterface
{
    private const string URL = 'https://running.life/kalendarz-biegow/polska';
    private const array MONTHS = [
        'sty' => 1,
        'lut' => 2,
        'mar' => 3,
        'kwi' => 4,
        'maj' => 5,
        'cze' => 6,
        'lip' => 7,
        'sie' => 8,
        'wrz' => 9,
        'paź' => 10,
        'lis' => 11,
        'gru' => 12,
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private VoivodeshipNormalizer $voivodeshipNormalizer,
        private DistanceNormalizer $distanceNormalizer,
    ) {
    }

    public function getName(): string
    {
        return 'running-life';
    }

    /** @return list<RawRaceData> */
    public function fetch(): array
    {
        $response = $this->httpClient->request('GET', self::URL);
        $html = $response->getContent();

        return $this->parseHtml($html);
    }

    /** @return list<RawRaceData> */
    private function parseHtml(string $html): array
    {
        $rawRaceDataList = [];

        $crawler = new Crawler($html);
        $crawler->filter('a.event-card-title-link')->each(function ($linkNode) use (&$rawRaceDataList) {
            $row = $linkNode->closest('.event-card-main');
            if (null === $row) {
                return;
            }

            $date = $this->parseDate($row->filter('.event-calendar')->first());
            if (null === $date) {
                return;
            }

            $url = $linkNode->attr('href');
            if (null === $url) {
                return;
            }

            $name = $row->filter('.event-card-title')->first()->text();
            $cityAndVoivodeship = $row->filter('.event-card-location')->first()->text();
            $city = $this->extractCity($cityAndVoivodeship);
            $voivodeship = $this->voivodeshipNormalizer->normalize($this->extractVoivodeship($cityAndVoivodeship));

            $distances = $row->filter('.event-card-distances-wrap a')->each(fn ($distanceNode) => $this->distanceNormalizer->normalize(trim($distanceNode->text())));
            $distances = array_values(array_filter($distances));

            $rawRaceDataList[] = new RawRaceData(
                $name,
                $date,
                $city,
                $voivodeship,
                $distances,
                $url,
                '',
            );
        });

        return $rawRaceDataList;
    }

    private function extractCity(string $cityAndVoivodeship): string
    {
        return trim(explode(',', $cityAndVoivodeship)[0]);
    }

    private function extractVoivodeship(string $cityAndVoivodeship): string
    {
        $parts = explode(',', $cityAndVoivodeship);

        return \count($parts) > 1 ? trim($parts[1]) : '';
    }

    private function parseDate(Crawler $calendarDiv): ?string
    {
        $divs = $calendarDiv->filter('div > div > div');
        if ($divs->count() < 2) {
            return null;
        }

        $monthStr = mb_strtolower(trim($divs->eq(0)->text()));
        $dayStr = trim($divs->eq(1)->text());

        // Range: "7-10" → bierz pierwszy dzień
        $day = (int) explode('-', $dayStr)[0];

        $month = self::MONTHS[$monthStr] ?? null;
        if (null === $month || 0 === $day) {
            return null;
        }

        // Rok: jeśli miesiąc już minął, to następny rok
        $currentMonth = (int) date('n');
        $year = (int) date('Y');
        if ($month < $currentMonth) {
            ++$year;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-n-j', "$year-$month-$day");

        return $date ? $date->format('Y-m-d') : null;
    }
}
