<?php

declare(strict_types=1);

namespace App\Review\Infrastructure\Security;

use App\Review\Domain\Model\Review;
use App\Shared\Domain\Security\AuthenticatedUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Review>
 */
final class ReviewVoter extends Voter
{
    public const string DELETE = 'REVIEW_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::DELETE === $attribute && $subject instanceof Review;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof AuthenticatedUserInterface) {
            return false;
        }

        return $subject->getUserId()->equals($user->getId());
    }
}
