<?php

declare(strict_types=1);

namespace Tests\Unit\Review\Domain\Model;

use App\RaceCatalog\Domain\Model\RaceId;
use App\Review\Domain\Model\Review;
use App\Review\Domain\Model\ReviewId;
use App\UserProfile\Domain\Model\UserId;
use PHPUnit\Framework\TestCase;

class ReviewTest extends TestCase
{
    public function testCreateReview(): void
    {
        $userId = UserId::generate();
        $raceId = RaceId::generate();
        $review = Review::create(
            ReviewId::generate(),
            $userId,
            $raceId,
            4,
            'test comment',
        );

        $this->assertEquals(4, $review->getRating());
        $this->assertEquals('test comment', $review->getComment());
        $this->assertEquals($userId->toString(), $review->getUserId()->toString());
        $this->assertEquals($raceId->toString(), $review->getRaceId()->toString());
    }

    public function testThrowsOnRatingBelowOne(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Review::create(
            ReviewId::generate(),
            UserId::generate(),
            RaceId::generate(),
            0,
            'test comment',
        );
    }

    public function testThrowsOnRatingAboveFive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Review::create(
            ReviewId::generate(),
            UserId::generate(),
            RaceId::generate(),
            6,
            'test comment',
        );
    }

    public function testAcceptsEmptyComment(): void
    {
        $review = Review::create(
            ReviewId::generate(),
            UserId::generate(),
            RaceId::generate(),
            4,
        );

        $this->assertEquals('', $review->getComment());
    }

    public function testAcceptsMinAndMaxRating(): void
    {
        $reviewMin = Review::create(
            ReviewId::generate(),
            UserId::generate(),
            RaceId::generate(),
            1,
            'test comment',
        );
        $this->assertEquals(1, $reviewMin->getRating());

        $reviewMax = Review::create(
            ReviewId::generate(),
            UserId::generate(),
            RaceId::generate(),
            5,
            'test comment',
        );
        $this->assertEquals(5, $reviewMax->getRating());
    }
}
