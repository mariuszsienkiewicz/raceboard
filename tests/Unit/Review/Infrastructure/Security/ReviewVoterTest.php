<?php

declare(strict_types=1);

namespace App\Tests\Unit\Review\Infrastructure\Security;

use App\Review\Domain\Model\Review;
use App\Review\Domain\Model\ReviewId;
use App\Review\Infrastructure\Security\ReviewVoter;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use App\Shared\Domain\Security\AuthenticatedUserInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ReviewVoterTest extends TestCase
{
    private ReviewVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new ReviewVoter();
    }

    public function testGrantsDeleteToOwner(): void
    {
        $ownerId = UserId::generate();
        $review = $this->reviewFor($ownerId);

        $result = $this->voter->vote(
            $this->tokenWithUser($this->authenticatedUser($ownerId)),
            $review,
            [ReviewVoter::DELETE],
        );

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testDeniesDeleteToOtherUser(): void
    {
        $review = $this->reviewFor(UserId::generate());

        $result = $this->voter->vote(
            $this->tokenWithUser($this->authenticatedUser(UserId::generate())),
            $review,
            [ReviewVoter::DELETE],
        );

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testDeniesWhenUserIsNotAuthenticatedUser(): void
    {
        $review = $this->reviewFor(UserId::generate());

        // no user = null
        $result = $this->voter->vote(
            $this->tokenWithUser(null),
            $review,
            [ReviewVoter::DELETE],
        );

        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testAbstainsForUnsupportedAttributeOrSubject(): void
    {
        $ownerId = UserId::generate();
        $review = $this->reviewFor($ownerId);
        $token = $this->tokenWithUser($this->authenticatedUser($ownerId));

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, $review, ['REVIEW_EDIT']),
        );

        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($token, new \stdClass(), [ReviewVoter::DELETE]),
        );
    }

    private function reviewFor(UserId $ownerId): Review
    {
        return Review::create(
            ReviewId::generate(),
            $ownerId,
            RaceId::generate(),
            4,
            'ok',
        );
    }

    private function tokenWithUser(?UserInterface $user): TokenInterface
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }

    private function authenticatedUser(UserId $id): AuthenticatedUserInterface
    {
        $user = $this->createStub(AuthenticatedUserInterface::class);
        $user->method('getId')->willReturn($id);

        return $user;
    }
}
