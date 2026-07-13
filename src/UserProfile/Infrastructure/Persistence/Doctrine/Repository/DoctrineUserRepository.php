<?php

declare(strict_types=1);

namespace App\UserProfile\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Model\UserId;
use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function findById(UserId $id): ?User
    {
        return $this->entityManager->find(User::class, $id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByIds(array $ids): array
    {
        if ([] === $ids) {
            return [];
        }

        $stringIds = array_map(fn (UserId $id) => $id->toString(), $ids);

        $users = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $stringIds)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($users as $user) {
            $indexed[$user->getId()->toString()] = $user;
        }

        return $indexed;
    }
}
