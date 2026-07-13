<?php

declare(strict_types=1);

namespace App\Tests\Integration\Review\Infrastructure\Persistence;

use App\Review\Domain\Model\Review;
use App\Review\Domain\Model\ReviewId;
use App\Review\Domain\Repository\ReviewRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineReviewRepositoryTest extends KernelTestCase
{
    private ReviewRepositoryInterface $repository;
    private \Doctrine\ORM\EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $repository = self::getContainer()->get(ReviewRepositoryInterface::class);
        assert($repository instanceof ReviewRepositoryInterface);
        $this->repository = $repository;

        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        assert($em instanceof \Doctrine\ORM\EntityManagerInterface);
        $this->em = $em;
    }

    public function testSavesAndFindsByRace(): void
    {
        $review = Review::create(
            ReviewId::generate(),
            UserId::generate(),
            RaceId::generate(),
            5,
            'Great race!',
        );

        $this->repository->save($review);
        $this->em->clear();

        $reviewsByRace = $this->repository->findByRace($review->getRaceId(), 10, 0);
        $this->assertCount(1, $reviewsByRace);
        $this->assertEquals($review->getId()->toString(), $reviewsByRace[0]->getId()->toString());
    }

    public function testFindsByUserAndRace(): void
    {
        $review = Review::create(
            ReviewId::generate(),
            UserId::generate(),
            RaceId::generate(),
            5,
            'Great race!',
        );

        $this->repository->save($review);
        $this->em->clear();

        $foundReview = $this->repository->findByUserAndRace($review->getUserId(), $review->getRaceId());
        $this->assertNotNull($foundReview);
        $this->assertEquals($review->getId()->toString(), $foundReview->getId()->toString());
    }

    public function testFindByUserAndRaceReturnsNullWhenNotFound(): void
    {
        $foundReview = $this->repository->findByUserAndRace(UserId::generate(), RaceId::generate());
        $this->assertNull($foundReview);
    }

    public function testFindsByUser(): void
    {
        $review = Review::create(
            ReviewId::generate(),
            UserId::generate(),
            RaceId::generate(),
            5,
            'Great race!',
        );

        $this->repository->save($review);
        $this->em->clear();

        $reviewsByUser = $this->repository->findByUser($review->getUserId());
        $this->assertCount(1, $reviewsByUser);
        $this->assertEquals($review->getId()->toString(), $reviewsByUser[0]->getId()->toString());
    }

    public function testRemovesReview(): void
    {
        $review = Review::create(
            ReviewId::generate(),
            UserId::generate(),
            RaceId::generate(),
            5,
            'Great race!',
        );

        $this->repository->save($review);
        $this->em->clear();

        $reviewToRemove = $this->repository->findByUserAndRace($review->getUserId(), $review->getRaceId());
        $this->assertNotNull($reviewToRemove);

        $this->repository->remove($reviewToRemove);
        $this->em->clear();

        $foundReview = $this->repository->findByUserAndRace($review->getUserId(), $review->getRaceId());
        $this->assertNull($foundReview);
    }
}
