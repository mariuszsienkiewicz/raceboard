<?php

declare(strict_types=1);

namespace App\DataImport\Application;

class DateParser
{
    /** @var list<array{pattern: string, format: string}> */
    private const array STANDARD_FORMATS = [
        ['pattern' => '/^(\d{4}\.\d{1,2}\.\d{1,2})/', 'format' => 'Y.n.j'],
        ['pattern' => '/^(\d{1,2}\.\d{1,2}\.\d{4})/', 'format' => 'j.n.Y'],
        ['pattern' => '/^(\d{2}\.\d{2}\.\d{4})/', 'format' => 'd.m.Y'],
    ];

    /** @var array<string, int> */
    private const array POLISH_MONTHS = [
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

    public function parse(string $raw): ?\DateTimeImmutable
    {
        $cleaned = trim(explode(' ', trim($raw))[0]);

        // Range: "11-12.04.2026" - get the first date
        if (preg_match('/^(\d{1,2})-\d{1,2}\.(\d{2})\.(\d{4})/', $raw, $matches)) {
            return \DateTimeImmutable::createFromFormat('Y-m-j', "$matches[3]-$matches[2]-$matches[1]") ?: null;
        }

        foreach (self::STANDARD_FORMATS as $format) {
            if (preg_match($format['pattern'], $cleaned, $matches)) {
                return \DateTimeImmutable::createFromFormat($format['format'], $matches[1]) ?: null;
            }
        }

        return null;
    }

    public function parseWithoutYear(string $monthStr, string $dayStr): ?\DateTimeImmutable
    {
        $month = self::POLISH_MONTHS[mb_strtolower(trim($monthStr))] ?? null;
        $day = (int) explode('-', trim($dayStr))[0];

        if (null === $month || 0 === $day) {
            return null;
        }

        $currentMonth = (int) date('n');
        $year = (int) date('Y');
        if ($month < $currentMonth) {
            ++$year;
        }

        return \DateTimeImmutable::createFromFormat('Y-n-j', "$year-$month-$day") ?: null;
    }
}
