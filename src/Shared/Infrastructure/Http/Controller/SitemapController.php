<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Controller;

use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Shared\Application\Seo\SitemapBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SitemapController
{
    private const CACHE_MAX_AGE = 3600;

    public function __construct(
        private RaceRepositoryInterface $raceRepository,
        private SitemapBuilder $sitemapBuilder,
    ) {
    }

    #[Route('/sitemap.xml', name: 'sitemap', methods: ['GET'])]
    public function sitemap(Request $request): Response
    {
        $raceIds = [];
        foreach ($this->raceRepository->findAll() as $race) {
            $raceIds[] = $race->getIdString();
        }

        $xml = $this->sitemapBuilder->buildXml(
            $request->getSchemeAndHttpHost(),
            $raceIds,
        );

        $response = new Response($xml, Response::HTTP_OK, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
        $response->setPublic();
        $response->setMaxAge(self::CACHE_MAX_AGE);

        return $response;
    }

    #[Route('/robots.txt', name: 'robots', methods: ['GET'])]
    public function robots(Request $request): Response
    {
        $body = $this->sitemapBuilder->buildRobotsTxt(
            $request->getSchemeAndHttpHost(),
        );

        $response = new Response($body, Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
        $response->setPublic();
        $response->setMaxAge(self::CACHE_MAX_AGE);

        return $response;
    }
}
