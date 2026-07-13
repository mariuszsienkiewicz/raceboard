<?php

declare(strict_types=1);

namespace App\UserProfile\Application;

use App\Shared\Domain\Model\UserId;
use App\UserProfile\Domain\Exception\EmailAlreadyExistsException;
use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function handle(string $email, string $plainPassword, string $displayName): User
    {
        if (null !== $this->repository->findByEmail($email)) {
            throw EmailAlreadyExistsException::forEmail($email);
        }

        $user = User::create(UserId::generate(), $email, 'placeholder', $displayName);
        $user->updatePassword($this->hasher->hashPassword($user, $plainPassword));
        $this->repository->save($user);

        return $user;
    }
}
