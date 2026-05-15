<?php

declare(strict_types=1);

namespace App\UserProfile\Domain\Repository;

use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Model\UserId;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByEmail(string $email): ?User;
}
