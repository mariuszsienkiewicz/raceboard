<?php

declare(strict_types=1);

namespace App\DataImport\Application\Normalizer;

final class DistanceNormalizer
{
    /** @var array<string, float> */
    private const array NAMED_DISTANCES = [
        'maraton' => 42.195,
        'marathon' => 42.195,
        'półmaraton' => 21.0975,
        'half marathon' => 21.0975,
        'half-marathon' => 21.0975,
        'ultramaraton' => 100.0,
        'ultra' => 100.0,
        'piątka' => 5.0,
        'dziesiątka' => 10.0,
    ];

    /**
     * @return array{name: string, lengthInKm: float, priceInPln: float|null}|null
     */
    public function normalize(string $raw): ?array
    {
        $cleaned = mb_strtolower(trim($raw));

        if (isset(self::NAMED_DISTANCES[$cleaned])) {
            return [
                'name' => self::NAMED_DISTANCES[$cleaned].' km',
                'lengthInKm' => self::NAMED_DISTANCES[$cleaned],
                'priceInPln' => null,
            ];
        }

        if (preg_match('/^(\d+[.,]?\d*)\s*(?:km|k)$/i', $cleaned, $matches)) {
            $km = (float) str_replace(',', '.', $matches[1]);

            return [
                'name' => $km.' km',
                'lengthInKm' => $km,
                'priceInPln' => null,
            ];
        }

        if (preg_match('/^(\d+)\s*m$/i', $cleaned, $matches)) {
            $km = (int) $matches[1] / 1000;

            return [
                'name' => $km.' km',
                'lengthInKm' => $km,
                'priceInPln' => null,
            ];
        }

        if (preg_match('/^(\d+[.,]?\d*)$/', $cleaned, $matches)) {
            $km = (float) str_replace(',', '.', $matches[1]);

            return [
                'name' => $km.' km',
                'lengthInKm' => $km,
                'priceInPln' => null,
            ];
        }

        // "Duathlon", "Multisport" → skip
        return null;
    }
}
