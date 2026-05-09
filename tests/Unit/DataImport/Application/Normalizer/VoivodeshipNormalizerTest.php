<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataImport\Application\Normalizer;

use App\DataImport\Application\Normalizer\VoivodeshipNormalizer;
use PHPUnit\Framework\TestCase;

final class VoivodeshipNormalizerTest extends TestCase
{
    private VoivodeshipNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new VoivodeshipNormalizer();
    }

    public function testNormalizesPolishFullName(): void
    {
        $normalized = $this->normalizer->normalize('Województwo pomorskie');
        $this->assertSame('pomorskie', $normalized);
    }

    public function testNormalizesPolishShortName(): void
    {
        $normalized = $this->normalizer->normalize('pomorskie');
        $this->assertSame('pomorskie', $normalized);
    }

    public function testNormalizesEnglishName(): void
    {
        $normalized = $this->normalizer->normalize('Pomeranian Voivodeship');
        $this->assertSame('pomorskie', $normalized);
    }

    public function testNormalizesEnglishWithDash(): void
    {
        $normalized = $this->normalizer->normalize('Kuyavian-Pomeranian Voivodeship');
        $this->assertSame('kujawsko-pomorskie', $normalized);
    }

    public function testNormalizesLodzVoivodeship(): void
    {
        $normalized = $this->normalizer->normalize('Łódź Voivodeship');
        $this->assertSame('łódzkie', $normalized);
    }

    public function testNormalizesPodkarpackieVoivodeship(): void
    {
        $normalized = $this->normalizer->normalize('Podkarpackie Voivodeship');
        $this->assertSame('podkarpackie', $normalized);
    }

    // Edge cases
    public function testIsCaseInsensitive(): void
    {
        $normalized = $this->normalizer->normalize('POMORSKIE');
        $this->assertSame('pomorskie', $normalized);
    }

    public function testTrimsWhitespace(): void
    {
        $normalized = $this->normalizer->normalize('  pomorskie  ');
        $this->assertSame('pomorskie', $normalized);
    }

    public function testReturnsRawValueWhenUnknown(): void
    {
        $normalized = $this->normalizer->normalize('unknown region');
        $this->assertSame('unknown region', $normalized);
    }

    public function testHandlesEmptyString(): void
    {
        $normalized = $this->normalizer->normalize('');
        $this->assertSame('', $normalized);
    }

    public function testNormalizesPolishWithoutDiacritics(): void
    {
        $normalized = $this->normalizer->normalize('Wojewodztwo Lodzkie');
        $this->assertSame('łódzkie', $normalized);
    }

    public function testNormalizesWithAbbreviatedPrefix(): void
    {
        $normalized = $this->normalizer->normalize('woj. pomorskie');
        $this->assertSame('pomorskie', $normalized);
    }

    public function testNormalizesEnglishRegionalVariant(): void
    {
        $normalized = $this->normalizer->normalize('Voivodeship of Masovia');
        $this->assertSame('mazowieckie', $normalized);
    }

    public function testNormalizesWithoutHyphen(): void
    {
        $normalized = $this->normalizer->normalize('Warminsko Mazurskie');
        $this->assertSame('warmińsko-mazurskie', $normalized);
    }

    // Pokrycie wszystkich 16 województw
    public function testNormalizesAllSixteenVoivodeships(): void
    {
        foreach (self::voivodeshipProvider() as [$input, $expected]) {
            $normalized = $this->normalizer->normalize($input);
            $this->assertSame($expected, $normalized, "Failed to normalize '$input'");
        }
    }

    /** @return list<array{string, string}> */
    public static function voivodeshipProvider(): array
    {
        return [
            ['Pomeranian Voivodeship', 'pomorskie'],
            ['Województwo pomorskie', 'pomorskie'],
            ['Lesser Poland Voivodeship', 'małopolskie'],
            ['Województwo małopolskie', 'małopolskie'],
            ['Łódź Voivodeship', 'łódzkie'],
            ['Województwo łódzkie', 'łódzkie'],
            ['Kuyavian-Pomeranian Voivodeship', 'kujawsko-pomorskie'],
            ['Województwo kujawsko-pomorskie', 'kujawsko-pomorskie'],
            ['Masovian Voivodeship', 'mazowieckie'],
            ['Województwo mazowieckie', 'mazowieckie'],
            ['Silesian Voivodeship', 'śląskie'],
            ['Województwo śląskie', 'śląskie'],
            ['Greater Poland Voivodeship', 'wielkopolskie'],
            ['Województwo wielkopolskie', 'wielkopolskie'],
            ['West Pomeranian Voivodeship', 'zachodniopomorskie'],
            ['Województwo zachodniopomorskie', 'zachodniopomorskie'],
            ['Opole Voivodeship', 'opolskie'],
            ['Województwo opolskie', 'opolskie'],
            ['Lubusz Voivodeship', 'lubuskie'],
            ['Województwo lubuskie', 'lubuskie'],
            ['Podlaskie Voivodeship', 'podlaskie'],
            ['Województwo podlaskie', 'podlaskie'],
            ['Podkarpackie Voivodeship', 'podkarpackie'],
            ['Województwo podkarpackie', 'podkarpackie'],
            ['Holy Cross Voivodeship', 'świętokrzyskie'],
            ['Województwo świętokrzyskie', 'świętokrzyskie'],
            ['Warmian-Masurian Voivodeship', 'warmińsko-mazurskie'],
            ['Województwo warmińsko-mazurskie', 'warmińsko-mazurskie'],
            ['Lower Silesian Voivodeship', 'dolnośląskie'],
            ['Województwo dolnośląskie', 'dolnośląskie'],
            ['Lubelskie Voivodeship', 'lubelskie'],
            ['Województwo lubelskie', 'lubelskie'],
            ['Podkarpackie Voivodeship', 'podkarpackie'],
            ['woj. pomorskie', 'pomorskie'],
            ['Wojewodztwo Lodzkie', 'łódzkie'],
            ['Voivodeship of Masovia', 'mazowieckie'],
            ['Warminsko Mazurskie', 'warmińsko-mazurskie'],
        ];
    }
}
