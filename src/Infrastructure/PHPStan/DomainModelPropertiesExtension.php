<?php

declare(strict_types=1);

namespace App\Infrastructure\PHPStan;

use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

class DomainModelPropertiesExtension implements ReadWritePropertiesExtension
{
    public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
    {
        return str_contains(
            $property->getDeclaringClass()->getName(),
            'Domain\\Model'
        );
    }

    public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }
}
