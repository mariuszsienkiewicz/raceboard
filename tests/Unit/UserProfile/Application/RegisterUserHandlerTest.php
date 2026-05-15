<?php

declare(strict_types=1);

namespace App\Tests\Unit\UserProfile\Application;

use App\UserProfile\Application\RegisterUserHandler;
use App\UserProfile\Domain\Exception\EmailAlreadyExistsException;
use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Model\UserId;
use App\UserProfile\Domain\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterUserHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $repository;
    private RegisterUserHandler $handler;

    public function setUp(): void
    {
        $this->repository = $this->createMock(UserRepositoryInterface::class);
        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed_password_123');
        $this->handler = new RegisterUserHandler($this->repository, $hasher);
    }

    public function testRegistersNewUser(): void
    {
        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);
        $this->repository->expects($this->once())->method('save')->with($this->callback(
            fn (User $user) => 'test@example.com' === $user->getEmail(),
        ));

        $this->handler->handle('test@example.com', 'password');
    }

    public function testThrowsWhenEmailAlreadyExists(): void
    {
        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->willReturn(new User(UserId::generate(), 'test@example.com', 'hashed_password_123'));

        $this->expectException(EmailAlreadyExistsException::class);

        $this->handler->handle('test@example.com', 'password');
    }

    public function testHashesPassword(): void
    {
        $this->repository->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);
        $this->repository->expects($this->once())->method('save')->with($this->callback(
            fn (User $user) => 'password' !== $user->getPassword() && 'hashed_password_123' === $user->getPassword(),
        ));

        $this->handler->handle('test@example.com', 'password');
    }
}
