<?php

declare(strict_types=1);

namespace App\DataImport\Infrastructure\Scraper;

use App\DataImport\Application\DateParser;
use App\DataImport\Domain\ImportAdapterInterface;
use App\DataImport\Domain\RawRaceData;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MaratonyPolskieAdapter implements ImportAdapterInterface
{
    private const string URL = 'https://www.maratonypolskie.pl/mp_index.php';
    private const string DOMAIN = 'https://www.maratonypolskie.pl/';
    private const array POLISH_MONTHS = [
        1 => 'styczen',
        2 => 'luty',
        3 => 'marzec',
        4 => 'kwiecien',
        5 => 'maj',
        6 => 'czerwiec',
        7 => 'lipiec',
        8 => 'sierpien',
        9 => 'wrzesien',
        10 => 'pazdziernik',
        11 => 'listopad',
        12 => 'grudzien',
    ];

    public function __construct(private HttpClientInterface $httpClient, private DateParser $dateParser, private LoggerInterface $logger, private int $monthsAhead = 12, private int $delaySeconds = 2)
    {
    }

    public function getName(): string
    {
        return 'maratony-polskie';
    }

    /** @return list<RawRaceData> */
    public function fetch(): array
    {
        /** @var list<RawRaceData> */
        $allRaces = [];
        $now = new \DateTimeImmutable();

        for ($i = 0; $i < $this->monthsAhead; ++$i) {
            $month = $now->modify("+{$i} months");
            try {
                $monthRaces = $this->fetchMonth($month);
                $allRaces = [...$allRaces, ...$monthRaces];
                if ($this->delaySeconds > 0 && count($monthRaces) > 0) {
                    sleep($this->delaySeconds);
                }
            } catch (\Throwable $e) {
                $this->logger->warning(sprintf('Failed to fetch month %s: %s', $month->format('Y-m'), $e->getMessage()));
                continue;
            }
        }

        return $allRaces;
    }

    /** @return list<RawRaceData> */
    private function fetchMonth(\DateTimeImmutable $month): array
    {
        $monthNum = (int) $month->format('n');
        $year = $month->format('Y');
        $lastDay = $month->format('t');

        $response = $this->httpClient->request('POST', self::URL, [
            'body' => [
                'dzienp1' => '1',
                'dzienk1' => $lastDay,
                'czasm1' => self::POLISH_MONTHS[$monthNum],
                'czasr1' => $year,
                'wojew' => 'Wszystkie',
                'mapa_nazwa' => 'Polska',
                'mapa_tryb2' => 'Tekstowo',
                'grp' => '13',
                'cykl' => '',
                'wielkosc' => '',
                'dzial' => '3',
                'action' => '1',
            ],
        ]);
        $html = $response->getContent();

        return $this->parseHtml($html);
    }

    /** @return list<RawRaceData> */
    private function parseHtml(string $html): array
    {
        $rawRaceDataList = [];

        $crawler = new Crawler($html);
        $crawler->filter('a.czte')->each(function ($linkNode) use (&$rawRaceDataList) {
            $row = $linkNode->closest('tr');
            if (null === $row) {
                return;
            }

            $cells = $row->filter('td');

            $rawDate = trim($cells->eq(1)->text());
            $parsed = $this->dateParser->parse($rawDate);
            if (null === $parsed) {
                return;
            }
            $date = $parsed->format('Y-m-d');

            $city = $this->extractCity($cells->eq(2));

            $name = trim($linkNode->text());
            $url = $linkNode->attr('href');

            $rawRaceDataList[] = new RawRaceData(
                $name,
                $date,
                $city,
                '',
                [],
                self::DOMAIN.$url,
                '',
            );
        });

        return $rawRaceDataList;
    }

    private function extractCity(Crawler $cell): string
    {
        // sometimes city contains additional info, for example distances, so we need to extract only the city name
        // <font size="1" face="verdana" color="black">Kępno<p align="right">10 km</p></font>
        // and sometimes it contains only city name like here:
        // <font size="1" face="verdana" color="black">Jelcz-Laskowice</font>
        $fontNode = $cell->filter('font')->getNode(0);

        if (null === $fontNode) {
            return trim($cell->text());
        }

        foreach ($fontNode->childNodes as $child) {
            if (XML_TEXT_NODE === $child->nodeType) {
                $text = trim($child->nodeValue ?? '');
                if ('' !== $text) {
                    return $text;
                }
            }
        }

        return trim($fontNode->textContent);
    }
}
