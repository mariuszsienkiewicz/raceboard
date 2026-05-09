<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataImport\Application\Normalizer;

use App\DataImport\Application\Normalizer\DistanceNormalizer;
use PHPUnit\Framework\TestCase;

class DistanceNormalizerTest extends TestCase
{
    private DistanceNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new DistanceNormalizer();
    }

    public function testNormalizesFiveKm(): void
    {
        $normalized = $this->normalizer->normalize('5 km');
        $this->assertSame([
            'name' => '5 km',
            'lengthInKm' => 5.0,
            'priceInPln' => null,
        ], $normalized);
    }

    public function testNormalizesFiveK(): void
    {
        $normalized = $this->normalizer->normalize('5k');
        $this->assertSame([
            'name' => '5 km',
            'lengthInKm' => 5.0,
            'priceInPln' => null,
        ], $normalized);
    }

    public function testNormalizesFiveKUppercase(): void
    {
        $normalized = $this->normalizer->normalize('5K');
        $this->assertSame([
            'name' => '5 km',
            'lengthInKm' => 5.0,
            'priceInPln' => null,
        ], $normalized);
    }

    public function testNormalizesWithComma(): void
    {
        $normalized = $this->normalizer->normalize('5,5 km');
        $this->assertSame([
            'name' => '5.5 km',
            'lengthInKm' => 5.5,
            'priceInPln' => null,
        ], $normalized);
    }

    public function testNormalizesWithDot(): void
    {
        $normalized = $this->normalizer->normalize('10.5 km');
        $this->assertSame([
            'name' => '10.5 km',
            'lengthInKm' => 10.5,
            'priceInPln' => null,
        ], $normalized);
    }

    public function testNormalizesMeters(): void
    {
        $normalized = $this->normalizer->normalize('42195 m');
        $this->assertSame([
            'name' => '42.195 km',
            'lengthInKm' => 42.195,
            'priceInPln' => null,
        ], $normalized);
    }

    public function testNormalizesHalfMarathon(): void
    {
        $normalized = $this->normalizer->normalize('Półmaraton');
        $this->assertSame([
            'name' => '21.0975 km',
            'lengthInKm' => 21.0975,
            'priceInPln' => null,
        ], $normalized);
    }

    public function testNormalizesMarathon(): void
    {
        $normalized = $this->normalizer->normalize('maraton');
        $this->assertSame([
            'name' => '42.195 km',
            'lengthInKm' => 42.195,
            'priceInPln' => null,
        ], $normalized);
    }

    public function testReturnsNullForNonRunning(): void
    {
        $normalized = $this->normalizer->normalize('Duathlon');
        $this->assertNull($normalized);
    }

    public function testNormalizesNameToConsistentFormat(): void
    {
        $normalized = $this->normalizer->normalize('5k');
        if (null === $normalized) {
            $this->fail('Expected non-null result');
        }
        $this->assertSame('5 km', $normalized['name']);
    }
}
