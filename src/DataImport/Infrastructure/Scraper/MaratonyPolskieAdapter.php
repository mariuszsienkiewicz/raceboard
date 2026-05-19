<?php

declare(strict_types=1);

namespace App\DataImport\Infrastructure\Scraper;

use App\DataImport\Application\DateParser;
use App\DataImport\Domain\ImportAdapterInterface;
use App\DataImport\Domain\RawRaceData;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MaratonyPolskieAdapter implements ImportAdapterInterface
{
    private const string URL = 'https://www.maratonypolskie.pl/mp_index.php?dzial=3&action=1&grp=13';
    private const string DOMAIN = 'https://www.maratonypolskie.pl/';

    public function __construct(private HttpClientInterface $httpClient, private DateParser $dateParser)
    {
    }

    public function getName(): string
    {
        return 'maratony-polskie';
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
