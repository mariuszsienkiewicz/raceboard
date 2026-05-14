<?php

declare(strict_types=1);

namespace App\Tests\Unit\Search\Domain;

use App\Search\Domain\SearchQuery;
use PHPUnit\Framework\TestCase;

class SearchQueryTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $query = new SearchQuery();
        $this->assertSame('', $query->query);
        $this->assertSame(1, $query->page);
        $this->assertSame(20, $query->perPage);
        $this->assertNull($query->city);
        $this->assertNull($query->voivodeship);
    }

    public function testCustomValues(): void
    {
        $dateFrom = new \DateTimeImmutable('+4 days')->format('Y-m-d');
        $dateTo = new \DateTimeImmutable('+5 days')->format('Y-m-d');
        $query = new SearchQuery('test', 'Test City', 'Test Voivodeship', 10, $dateFrom, $dateTo, 2, 40);
        $this->assertSame('test', $query->query);
        $this->assertSame('Test City', $query->city);
        $this->assertSame('Test Voivodeship', $query->voivodeship);
        $this->assertSame($dateFrom, $query->dateFrom);
        $this->assertSame($dateTo, $query->dateTo);
        $this->assertSame(2, $query->page);
        $this->assertSame(40, $query->perPage);
    }
}
