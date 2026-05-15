<?php

declare(strict_types=1);

namespace App\UserProfile\Domain\Model;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private readonly UserId $id,
        private string $email,
        private string $passwordHash,
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        if ('' === $email) {
            throw new \InvalidArgumentException('Email cannot be empty.');
        }
    }

    public static function create(
        UserId $id,
        string $email,
        string $passwordHash,
    ): self {
        return new self($id, $email, $passwordHash);
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getIdString(): string
    {
        return $this->id->toString();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    public function updatePassword(string $newHash): void
    {
        $this->passwordHash = $newHash;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // not needed, as we don't store any temporary, sensitive data on the user
    }

    /** @return non-empty-string */
    public function getUserIdentifier(): string
    {
        /* @phpstan-ignore return.type */
        return $this->email;
    }
}
