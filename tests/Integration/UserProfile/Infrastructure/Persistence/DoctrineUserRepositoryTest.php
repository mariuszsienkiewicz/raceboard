<?php

declare(strict_types=1);

namespace App\Tests\Integration\UserProfile\Infrastructure\Persistence;

use App\Shared\Domain\Model\UserId;
use App\UserProfile\Domain\Model\User;
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

    public function testFindByIdsReturnsCorrectUsers(): void
    {
        // Create multiple users
        $user1Id = UserId::generate();
        $user2Id = UserId::generate();

        $user1 = User::create($user1Id, 'test1@example.com', 'hashed_password1', 'John Doe');
        $user2 = User::create($user2Id, 'test2@example.com', 'hashed_password2', 'Jane Doe');

        // Save the users
        $this->repository->save($user1);
        $this->repository->save($user2);

        // Clear the EntityManager to ensure we fetch a fresh instance from the database
        $this->em->clear();

        // Retrieve the users by their IDs
        $retrievedUsers = $this->repository->findByIds([$user1Id, $user2Id]);

        // Test that both users are retrieved correctly
        $this->assertCount(2, $retrievedUsers);
        $this->assertArrayHasKey($user1Id->toString(), $retrievedUsers);
        $this->assertArrayHasKey($user2Id->toString(), $retrievedUsers);
        $this->assertEquals($user1->getEmail(), $retrievedUsers[$user1Id->toString()]->getEmail());
        $this->assertEquals($user2->getEmail(), $retrievedUsers[$user2Id->toString()]->getEmail());
    }

    public function testFindByIdsReturnsEmptyArrayForEmptyInput(): void
    {
        $result = $this->repository->findByIds([]);
        $this->assertSame([], $result);
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
