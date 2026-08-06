<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Application\Seo;

use App\Shared\Application\Seo\SitemapBuilder;
use PHPUnit\Framework\TestCase;

class SitemapBuilderTest extends TestCase
{
    private SitemapBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new SitemapBuilder();
    }

    public function testBuildXmlIncludesStaticPagesAndRaces(): void
    {
        $xml = $this->builder->buildXml('https://raceboard.pl', [
            'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        ]);

        $this->assertStringContainsString('xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', $xml);
        $this->assertStringContainsString('<loc>https://raceboard.pl/</loc>', $xml);
        $this->assertStringContainsString('<loc>https://raceboard.pl/about</loc>', $xml);
        $this->assertStringContainsString('<loc>https://raceboard.pl/privacy</loc>', $xml);
        $this->assertStringContainsString('<loc>https://raceboard.pl/terms</loc>', $xml);
        $this->assertStringContainsString('<loc>https://raceboard.pl/cookie</loc>', $xml);
        $this->assertStringContainsString(
            '<loc>https://raceboard.pl/races/aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee</loc>',
            $xml,
        );
        $this->assertStringNotContainsString('/login', $xml);
        $this->assertStringNotContainsString('/watchlist', $xml);
    }

    public function testBuildXmlTrimsTrailingSlashFromBaseUrl(): void
    {
        $xml = $this->builder->buildXml('https://raceboard.pl/', []);

        $this->assertStringContainsString('<loc>https://raceboard.pl/</loc>', $xml);
        $this->assertStringNotContainsString('https://raceboard.pl//', $xml);
    }

    public function testBuildRobotsTxtDisallowsPrivatePathsAndPointsToSitemap(): void
    {
        $robots = $this->builder->buildRobotsTxt('https://raceboard.pl');

        $this->assertStringContainsString('User-agent: *', $robots);
        $this->assertStringContainsString('Disallow: /login', $robots);
        $this->assertStringContainsString('Disallow: /register', $robots);
        $this->assertStringContainsString('Disallow: /watchlist', $robots);
        $this->assertStringContainsString('Disallow: /account', $robots);
        $this->assertStringContainsString('Disallow: /api/', $robots);
        $this->assertStringContainsString('Sitemap: https://raceboard.pl/sitemap.xml', $robots);
    }
}
