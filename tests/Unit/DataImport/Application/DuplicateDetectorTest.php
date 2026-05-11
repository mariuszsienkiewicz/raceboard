<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataImport\Application;

use App\DataImport\Application\DuplicateDetector;
use App\RaceCatalog\Domain\Model\Race;
use PHPUnit\Framework\TestCase;

class DuplicateDetectorTest extends TestCase
{
    private DuplicateDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new DuplicateDetector();
    }

    public function testReturnsNullWhenNoCandidates(): void
    {
        $detected = $this->detector->findDuplicate('Maraton Warszawski', []);
        $this->assertNull($detected);
    }

    public function testReturnsNullWhenNoRaceMatches(): void
    {
        $race = $this->createStub(Race::class);
        $race->method('getName')->willReturn('Maraton Warszawski');

        $race2 = $this->createStub(Race::class);
        $race2->method('getName')->willReturn('Półmaraton Gdański');

        $detected = $this->detector->findDuplicate('Bieg Tatrzański', [$race, $race2]);
        $this->assertNull($detected);
    }

    public function testReturnsFirstMatchWhenMultipleRacesMatch(): void
    {
        $race1 = $this->createStub(Race::class);
        $race1->method('getName')->willReturn('Maraton Warszawski');

        $race2 = $this->createStub(Race::class);
        $race2->method('getName')->willReturn('Maraton Warszawski 2025');

        $detected = $this->detector->findDuplicate('Maraton Warszawski', [$race1, $race2]);
        $this->assertSame($race1, $detected);
    }

    public function testDetectesDuplicateWhenCandidateNameContainsSearchedName(): void
    {
        $race = $this->createStub(Race::class);
        $race->method('getName')->willReturn('48. Maraton Warszawski');

        $detected = $this->detector->findDuplicate('Maraton Warszawski', [$race]);
        $this->assertSame($race, $detected);
    }

    public function testDetectesDuplicateWhenSearchedNameContainsCandidateName(): void
    {
        $race = $this->createStub(Race::class);
        $race->method('getName')->willReturn('Maraton Warszawski');
        $detected = $this->detector->findDuplicate('Maraton Warszawski 2025 Official', [$race]);
        $this->assertSame($race, $detected);
    }

    public function testComparisonIsCaseInsensitive(): void
    {
        // "MARATON WARSZAWSKI" vs "Maraton Warszawski"
        // mb_strtolower
        $race = $this->createStub(Race::class);
        $race->method('getName')->willReturn('Maraton Warszawski');
        $detected = $this->detector->findDuplicate('MARATON WARSZAWSKI', [$race]);
        $this->assertSame($race, $detected);
    }

    public function testDetectesDuplicateAfterStrippingArabicEditionPrefix(): void
    {
        // "48. Maraton Warszawski" vs "Maraton Warszawski"
        // After stripEditionNumbers both → "maraton warszawski" → equals, it returns true before even reaching Levenshtein
        $race = $this->createStub(Race::class);
        $race->method('getName')->willReturn('Maraton Warszawski');
        $detected = $this->detector->findDuplicate('48. Maraton Warszawski', [$race]);
        $this->assertSame($race, $detected);
    }

    public function testDetectesDuplicateAfterStrippingRomanNumeralPrefix(): void
    {
        // "XIII Maraton Warszawski" vs "Maraton Warszawski"
        // Regex /^[IVXLCDM]+\s+/i removes the prefix
        $race = $this->createStub(Race::class);
        $race->method('getName')->willReturn('Maraton Warszawski');
        $detected = $this->detector->findDuplicate('XIII Maraton Warszawski', [$race]);
        $this->assertSame($race, $detected);
    }

    public function testStripsEditionFromBothSidesAndStillMatches(): void
    {
        // "48. Maraton Warszawski" vs "XIII Maraton Warszawski"
        // Both stripped → "maraton warszawski" → equals, it returns true before even reaching Levenshtein
        $race = $this->createStub(Race::class);
        $race->method('getName')->willReturn('XIII Maraton Warszawski');
        $detected = $this->detector->findDuplicate('48. Maraton Warszawski', [$race]);
        $this->assertSame($race, $detected);
    }

    public function testDetectesDuplicateWithSmallTypo(): void
    {
        // "Maraton Warzsawski" vs "Maraton Warszawski" — 1 symbol off
        // distance/maxLen < 0.2 → similar
        $race = $this->createStub(Race::class);
        $race->method('getName')->willReturn('Maraton Warszawski');
        $detected = $this->detector->findDuplicate('Maraton Warzsawski', [$race]);
        $this->assertSame($race, $detected);
    }

    public function testReturnNullWhenLevenshteinDistanceExceedsThreshold(): void
    {
        // "Maraton Warszawski" vs "Bieg Piastów"
        // distance/maxLen >= 0.2 → not a duplicate
        $race = $this->createStub(Race::class);
        $race->method('getName')->willReturn('Maraton Warszawski');
        $detected = $this->detector->findDuplicate('Bieg Piastów', [$race]);
        $this->assertNull($detected);
    }

    public function testReturnNullWhenBothNamesAreEmptyAfterStripping(): void
    {
        // Edge case: maxLen === 0 — guard $maxLen > 0 protects against division by zero
        // Two names consisting only of digits/numbers e.g. "48." vs "XIII"
        $race = $this->createStub(Race::class);
        $race->method('getName')->willReturn('48.');
        $detected = $this->detector->findDuplicate('XIII', [$race]);
        $this->assertNull($detected);
    }

    public function testShortNamesAreNotFalsePositives(): void
    {
        // "Bieg" vs "Marsz" — short names, Levenshtein = 4, maxLen = 5 → 0.8 > 0.2 → null
        // Verifies that we don't allow too much through the 20% threshold for short names
        $race = $this->createStub(Race::class);
        $race->method('getName')->willReturn('Bieg');
        $detected = $this->detector->findDuplicate('Marsz', [$race]);
        $this->assertNull($detected);
    }
}
