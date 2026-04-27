<?php 

namespace App\Shared\Domain;

abstract class AbstractId
{
    final private function __construct(private readonly string $value) {}

    public static function generate(): static
    {
        return new static(sprintf(
            '%s-%s-%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(6)),
        ));
    }

    public static function fromString(string $value): static
    {
        return new static($value);
    }

    public function toString(): string { return $this->value; }

    public function equals(self $other): bool { return $this->value === $other->value; }

    public function __toString(): string { return $this->value; }
}
