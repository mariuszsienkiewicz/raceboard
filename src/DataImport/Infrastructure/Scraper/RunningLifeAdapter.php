<?php

declare(strict_types=1);

namespace App\DataImport\Infrastructure\Scraper;

use App\DataImport\Application\DateParser;
use App\DataImport\Application\Normalizer\DistanceNormalizer;
use App\DataImport\Application\Normalizer\VoivodeshipNormalizer;
use App\DataImport\Domain\ImportAdapterInterface;
use App\DataImport\Domain\RawRaceData;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RunningLifeAdapter implements ImportAdapterInterface
{
    private const string URL = 'https://running.life/kalendarz-biegow/polska';

    public function __construct(
        private HttpClientInterface $httpClient,
        private VoivodeshipNormalizer $voivodeshipNormalizer,
        private DistanceNormalizer $distanceNormalizer,
        private DateParser $dateParser,
        private LoggerInterface $logger,
        private int $delaySeconds = 2,
    ) {
    }

    public function getName(): string
    {
        return 'running-life';
    }

    /** @return list<RawRaceData> */
    public function fetch(): array
    {
        /** @var list<RawRaceData> */
        $allRaces = [];
        $maxPages = 100;
        $page = 1;
        while ($page <= $maxPages) {
            try {
                $pageRaces = $this->fetchPage($page);
                if (empty($pageRaces)) {
                    break;
                }
                $allRaces = [...$allRaces, ...$pageRaces];
            } catch (\Throwable $e) {
                $this->logger->warning(sprintf('Failed to fetch page %s: %s', $page, $e->getMessage()));
                break;
            }
            ++$page;
            if ($this->delaySeconds > 0) {
                sleep($this->delaySeconds);
            }
        }

        return $allRaces;
    }

    /** @return list<RawRaceData> */
    private function fetchPage(int $page): array
    {
        $response = $this->httpClient->request('GET', self::URL.'?page='.$page);
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

        $date = $this->dateParser->parseWithoutYear(
            $divs->eq(0)->text(),
            $divs->eq(1)->text(),
        );

        return $date?->format('Y-m-d');
    }
}
