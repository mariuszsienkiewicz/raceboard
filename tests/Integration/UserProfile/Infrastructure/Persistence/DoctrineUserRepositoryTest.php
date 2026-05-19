<?php

declare(strict_types=1);

namespace App\Tests\Integration\UserProfile\Infrastructure\Persistence;

use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Model\UserId;
use App\UserProfile\Domain\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineUserRepositoryTest extends KernelTestCase
{
    private UserRepositoryInterface $repository;
    private \Doctrine\ORM\EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $repository = self::getContainer()->get(UserRepositoryInterface::class);
        assert($repository instanceof UserRepositoryInterface);
        $this->repository = $repository;

        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        assert($em instanceof \Doctrine\ORM\EntityManagerInterface);
        $this->em = $em;
    }

    public function testSavingAndRetrievingUser(): void
    {
        // Create a new user
        $user = User::create(
            UserId::generate(),
            'test@example.com',
            'hashed_password',
            'John Doe',
        );

        // Save the user
        $this->repository->save($user);

        // Clear the EntityManager to ensure we fetch a fresh instance from the database
        $this->em->clear();

        // Retrieve the user
        $retrievedUser = $this->repository->findById($user->getId());

        // Test that the retrieved user matches the original
        $this->assertNotNull($retrievedUser);
        $this->assertEquals($user->getId(), $retrievedUser->getId());
        $this->assertEquals($user->getEmail(), $retrievedUser->getEmail());
        $this->assertEquals($user->getPassword(), $retrievedUser->getPassword());
    }

    public function testFindByEmail(): void
    {
        // Create a new user
        $user = User::create(
            UserId::generate(),
            'test@example.com',
            'hashed_password',
            'John Doe',
        );

        // Save the user
        $this->repository->save($user);

        // Clear the EntityManager to ensure we fetch a fresh instance from the database
        $this->em->clear();

        // Retrieve the user by email
        $retrievedUser = $this->repository->findByEmail($user->getEmail());

        // Test that the retrieved user matches the original
        $this->assertNotNull($retrievedUser);
        $this->assertEquals($user->getId(), $retrievedUser->getId());
        $this->assertEquals($user->getEmail(), $retrievedUser->getEmail());
        $this->assertEquals($user->getPassword(), $retrievedUser->getPassword());
    }

    public function testFindByEmailReturnsNullForNonExistent(): void
    {
        // Attempt to retrieve a user with a non-existent email
        $retrievedUser = $this->repository->findByEmail('test@example.com');

        // Test that the retrieved user is null
        $this->assertNull($retrievedUser);
    }

    public function testEmailIsUnique(): void
    {
        // Create a new user
        $user1 = User::create(
            UserId::generate(),
            'test@example.com',
            'hashed_password',
            'John Doe',
        );

        // Save the user
        $this->repository->save($user1);

        // Clear the EntityManager to ensure we fetch a fresh instance from the database
        $this->em->clear();

        // Create another user with the same email
        $user2 = User::create(
            UserId::generate(),
            'test@example.com',
            'hashed_password2',
            'Jane Doe',
        );

        // Expect an exception when trying to save the second user with the same email
        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);
        $this->repository->save($user2);
    }
}
