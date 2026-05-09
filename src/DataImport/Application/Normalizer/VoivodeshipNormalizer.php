<?php

declare(strict_types=1);

namespace App\DataImport\Application\Normalizer;

final class VoivodeshipNormalizer
{
    /** @var array<string, string> */
    private const array MAP = [
        // dolnoslaskie
        'dolnoslaskie' => 'dolnośląskie',
        'dolny slask' => 'dolnośląskie',
        'lower silesia' => 'dolnośląskie',
        'lower silesian' => 'dolnośląskie',
        'lower silesian voivodeship' => 'dolnośląskie',

        // kujawsko-pomorskie
        'kujawsko pomorskie' => 'kujawsko-pomorskie',
        'kuyavian pomeranian' => 'kujawsko-pomorskie',
        'kuyavian pomeranian voivodeship' => 'kujawsko-pomorskie',
        'kuyavia pomerania' => 'kujawsko-pomorskie',

        // lubelskie
        'lubelskie' => 'lubelskie',
        'lublin' => 'lubelskie',
        'lublin voivodeship' => 'lubelskie',

        // lubuskie
        'lubuskie' => 'lubuskie',
        'lubusz' => 'lubuskie',
        'lubusz voivodeship' => 'lubuskie',

        // lodzkie
        'lodzkie' => 'łódzkie',
        'lodz' => 'łódzkie',
        'lodz voivodeship' => 'łódzkie',

        // malopolskie
        'malopolskie' => 'małopolskie',
        'lesser poland' => 'małopolskie',
        'lesser poland voivodeship' => 'małopolskie',

        // mazowieckie
        'mazowieckie' => 'mazowieckie',
        'mazowsze' => 'mazowieckie',
        'masovia' => 'mazowieckie',
        'masovian' => 'mazowieckie',
        'masovian voivodeship' => 'mazowieckie',

        // opolskie
        'opolskie' => 'opolskie',
        'opole' => 'opolskie',
        'opole voivodeship' => 'opolskie',

        // podkarpackie
        'podkarpackie' => 'podkarpackie',
        'subcarpathia' => 'podkarpackie',
        'subcarpathian' => 'podkarpackie',
        'subcarpathian voivodeship' => 'podkarpackie',

        // podlaskie
        'podlaskie' => 'podlaskie',
        'podlasie' => 'podlaskie',
        'podlaskie voivodeship' => 'podlaskie',

        // pomorskie
        'pomorskie' => 'pomorskie',
        'pomerania' => 'pomorskie',
        'pomeranian' => 'pomorskie',
        'pomeranian voivodeship' => 'pomorskie',

        // slaskie
        'slaskie' => 'śląskie',
        'gorny slask' => 'śląskie',
        'silesia' => 'śląskie',
        'silesian' => 'śląskie',
        'silesian voivodeship' => 'śląskie',

        // swietokrzyskie
        'swietokrzyskie' => 'świętokrzyskie',
        'holy cross' => 'świętokrzyskie',
        'holy cross voivodeship' => 'świętokrzyskie',

        // warminsko-mazurskie
        'warminsko mazurskie' => 'warmińsko-mazurskie',
        'warmia mazury' => 'warmińsko-mazurskie',
        'warmian masurian' => 'warmińsko-mazurskie',
        'warmian masurian voivodeship' => 'warmińsko-mazurskie',

        // wielkopolskie
        'wielkopolskie' => 'wielkopolskie',
        'wielkopolska' => 'wielkopolskie',
        'greater poland' => 'wielkopolskie',
        'greater poland voivodeship' => 'wielkopolskie',

        // zachodniopomorskie
        'zachodniopomorskie' => 'zachodniopomorskie',
        'zachodnie pomorze' => 'zachodniopomorskie',
        'west pomerania' => 'zachodniopomorskie',
        'west pomeranian' => 'zachodniopomorskie',
        'west pomeranian voivodeship' => 'zachodniopomorskie',
    ];

    /** @var list<string> */
    private const array ADMINISTRATIVE_TERMS = [
        'woj',
        'wojewodztwo',
        'wojewodztwa',
        'voivodeship',
        'province',
        'region',
        'of',
    ];

    private const string SPACE_PATTERN = '/[^a-z0-9]+/u';

    public function normalize(string $raw): string
    {
        $key = $this->normalizeKey($raw);

        if ('' === $key) {
            return $raw;
        }

        if (isset(self::MAP[$key])) {
            return self::MAP[$key];
        }

        $withoutAdministrativeTerms = $this->stripAdministrativeTerms($key);
        if ('' !== $withoutAdministrativeTerms && isset(self::MAP[$withoutAdministrativeTerms])) {
            return self::MAP[$withoutAdministrativeTerms];
        }

        $compact = str_replace(' ', '', $withoutAdministrativeTerms);
        if ('' !== $compact && isset(self::MAP[$compact])) {
            return self::MAP[$compact];
        }

        $isoCode = $this->extractIsoCode($key);
        if (null !== $isoCode && isset(self::MAP[$isoCode])) {
            return self::MAP[$isoCode];
        }

        return $raw;
    }

    private function normalizeKey(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = $this->transliteratePolishChars($value);
        $value = (string) preg_replace(self::SPACE_PATTERN, ' ', $value);

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    private function transliteratePolishChars(string $value): string
    {
        return strtr($value, [
            'ą' => 'a',
            'ć' => 'c',
            'ę' => 'e',
            'ł' => 'l',
            'ń' => 'n',
            'ó' => 'o',
            'ś' => 's',
            'ź' => 'z',
            'ż' => 'z',
        ]);
    }

    private function stripAdministrativeTerms(string $normalizedValue): string
    {
        $tokens = explode(' ', $normalizedValue);
        $tokens = array_values(array_filter(
            $tokens,
            static fn (string $token): bool => !in_array($token, self::ADMINISTRATIVE_TERMS, true),
        ));

        return implode(' ', $tokens);
    }

    private function extractIsoCode(string $normalizedValue): ?string
    {
        if (1 === preg_match('/\bpl\s*([a-z]{2})\b/u', $normalizedValue, $matches)) {
            return 'pl '.$matches[1];
        }

        return null;
    }
}
