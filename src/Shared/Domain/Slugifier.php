<?php

declare(strict_types=1);

namespace App\Shared\Domain;

final class Slugifier
{
    public static function slugify(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text) ?: $text;
        $text = strtolower($text);
        $text = (string) preg_replace('/[^a-z0-9]+/', '-', $text);

        return trim($text, '-');
    }
}
