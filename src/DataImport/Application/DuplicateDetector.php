<?php

declare(strict_types=1);

namespace App\DataImport\Application;

use App\RaceCatalog\Domain\Model\Race;

class DuplicateDetector
{
    /**
     * @param list<Race> $existingRaces
     */
    public function findDuplicate(string $name, array $existingRaces): ?Race
    {
        foreach ($existingRaces as $race) {
            if ($this->isNameSimilar($name, $race->getName())) {
                return $race;
            }
        }

        return null;
    }

    private function isNameSimilar(string $a, string $b): bool
    {
        $a = mb_strtolower($a);
        $b = mb_strtolower($b);

        // "Maraton Warszawski" in "48. Maraton Warszawski"
        if (str_contains($a, $b) || str_contains($b, $a)) {
            return true;
        }

        // Levenshtein on normalized strings (removed digits, editions)
        $cleanA = $this->stripEditionNumbers($a);
        $cleanB = $this->stripEditionNumbers($b);

        if ($cleanA === $cleanB) {
            return true;
        }

        $distance = levenshtein($cleanA, $cleanB);
        $maxLen = max(mb_strlen($cleanA), mb_strlen($cleanB));

        // similar if distance is less than 20% of the length of the longer name
        return $maxLen > 0 && ($distance / $maxLen) < 0.2;
    }

    private function stripEditionNumbers(string $name): string
    {
        $cleaned = (string) preg_replace('/^\d+\.\s*/', '', $name);          // "48. " prefix
        $cleaned = (string) preg_replace('/^[IVXLCDM]+\s+/i', '', $cleaned); // "XIII " prefix

        return trim($cleaned);
    }
}
