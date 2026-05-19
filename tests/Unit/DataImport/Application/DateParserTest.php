<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataImport\Application;

use App\DataImport\Application\DateParser;
use PHPUnit\Framework\TestCase;

class DateParserTest extends TestCase
{
    private DateParser $parser;

    public function setUp(): void
    {
        $this->parser = new DateParser();
    }

    public function testParsesYearFirst(): void
    {
        $this->assertEquals('2026-04-30', $this->parser->parse('2026.4.30')?->format('Y-m-d'));
    }

    public function testParsesDayFirst(): void
    {
        $this->assertEquals('2026-04-01', $this->parser->parse('1.4.2026')?->format('Y-m-d'));
    }

    public function testParsesDayMonthPadded(): void
    {
        $this->assertEquals('2026-05-31', $this->parser->parse('31.05.2026')?->format('Y-m-d'));
    }

    public function testParsesDayRange(): void
    {
        $this->assertEquals('2026-04-11', $this->parser->parse('11-12.04.2026')?->format('Y-m-d'));
    }

    public function testReturnsNullForInvalid(): void
    {
        $this->assertNull($this->parser->parse('abc'));
    }

    public function testReturnsNullForEmpty(): void
    {
        $this->assertNull($this->parser->parse(''));
    }

    public function testStripsTrailingText(): void
    {
        $this->assertEquals('2026-04-01', $this->parser->parse('1.4.2026 (sr)')?->format('Y-m-d'));
    }

    public function testParsesPolishMonthAndDay(): void
    {
        $result = $this->parser->parseWithoutYear('maj', '8');
        $this->assertNotNull($result);
        $this->assertEquals('2026-05-08', $result->format('Y-m-d'));
    }

    public function testParsesPolishMonthWithRange(): void
    {
        $result = $this->parser->parseWithoutYear('maj', '7-10');
        $this->assertNotNull($result);
        $this->assertEquals('2026-05-07', $result->format('Y-m-d'));
    }

    public function testReturnsNullForUnknownMonth(): void
    {
        $result = $this->parser->parseWithoutYear('xyz', '8');
        $this->assertNull($result);
    }

    public function testReturnsNullForZeroDay(): void
    {
        $result = $this->parser->parseWithoutYear('maj', '0');
        $this->assertNull($result);
    }

    public function testInfersNextYearWhenMonthHasPassed(): void
    {
        $currentMonth = (int) date('n');
        $monthStr = 'sty';
        $dayStr = '15';

        $result = $this->parser->parseWithoutYear($monthStr, $dayStr);
        $this->assertNotNull($result);

        $expectedYear = (1 < $currentMonth) ? (int) date('Y') + 1 : (int) date('Y');
        $expectedDate = sprintf('%d-01-15', $expectedYear);
        $this->assertEquals($expectedDate, $result->format('Y-m-d'));
    }

    public function testInfersCurrentYearWhenMonthAhead(): void
    {
        $result = $this->parser->parseWithoutYear('gru', '10');

        $this->assertNotNull($result);
        $this->assertEquals((int) date('Y'), (int) $result->format('Y'));
        $this->assertEquals('12-10', $result->format('m-d'));
    }
}
