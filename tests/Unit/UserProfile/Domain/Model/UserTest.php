<?php

declare(strict_types=1);

namespace App\Tests\Unit\UserProfile\Domain\Model;

use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Model\UserId;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCreateUser(): void
    {
        $id = UserId::generate();
        $user = User::create(
            $id,
            'test@example.com',
            'hashed_password',
        );

        $this->assertSame($id, $user->getId());
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = User::create(
            UserId::generate(),
            'test@example.com',
            'hashed_password',
        );

        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testGetRolesReturnsRoleUser(): void
    {
        $user = User::create(
            UserId::generate(),
            'test@example.com',
            'hashed_password',
        );

        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testGetPasswordReturnsHash(): void
    {
        $user = User::create(
            UserId::generate(),
            'test@example.com',
            'hashed_password',
        );

        $this->assertSame('hashed_password', $user->getPassword());
    }

    public function testThrowsOnEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new User(
            UserId::generate(),
            '',
            'hashed_password',
        );
    }

    public function testEraseCredentialsDoesNotClearPassword(): void
    {
        $user = User::create(
            UserId::generate(),
            'test@example.com',
            'hashed_password',
        );

        $user->eraseCredentials();

        $this->assertSame('hashed_password', $user->getPassword());
    }
}
