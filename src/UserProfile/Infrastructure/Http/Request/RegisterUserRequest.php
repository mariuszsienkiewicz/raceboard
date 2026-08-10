<?php

declare(strict_types=1);

namespace App\UserProfile\Infrastructure\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email(mode: 'strict')]
        public string $email = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 4096)]
        public string $password = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 100)]
        public string $displayName = '',
    ) {
    }
}
