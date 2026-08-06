<?php

declare(strict_types=1);

namespace App\Shared\Application\Seo;

final class SitemapBuilder
{
    private const STATIC_PAGES = [
        ['path' => '/', 'priority' => '1.0'],
        ['path' => '/about', 'priority' => '0.5'],
        ['path' => '/privacy', 'priority' => '0.3'],
        ['path' => '/terms', 'priority' => '0.3'],
        ['path' => '/cookie', 'priority' => '0.3'],
    ];

    /**
     * @param list<string> $raceIds
     */
    public function buildXml(string $baseUrl, array $raceIds): string
    {
        $baseUrl = rtrim($baseUrl, '/');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach (self::STATIC_PAGES as $page) {
            $xml .= $this->urlElement($baseUrl.$page['path'], $page['priority']);
        }

        foreach ($raceIds as $raceId) {
            $xml .= $this->urlElement($baseUrl.'/races/'.$raceId, '0.8');
        }

        $xml .= '</urlset>';

        return $xml;
    }

    public function buildRobotsTxt(string $baseUrl): string
    {
        $baseUrl = rtrim($baseUrl, '/');

        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /watchlist',
            'Disallow: /account',
            'Disallow: /api/',
            '',
            'Sitemap: '.$baseUrl.'/sitemap.xml',
            '',
        ]);
    }

    private function urlElement(string $loc, string $priority): string
    {
        return '  <url>'."\n"
            .'    <loc>'.htmlspecialchars($loc, \ENT_XML1).'</loc>'."\n"
            .'    <priority>'.$priority.'</priority>'."\n"
            .'  </url>'."\n";
    }
}
