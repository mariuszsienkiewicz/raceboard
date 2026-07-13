<?php

declare(strict_types=1);

namespace App\UserProfile\Domain\Repository;

use App\Shared\Domain\Model\UserId;
use App\UserProfile\Domain\Model\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByEmail(string $email): ?User;

    /**
     * @param list<UserId> $ids
     *
     * @return array<string, User>
     */
    public function findByIds(array $ids): array;
}
