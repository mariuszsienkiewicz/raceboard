<?php

declare(strict_types=1);

namespace App\Shared\Domain\Security;

use App\Shared\Domain\Model\UserId;
use Symfony\Component\Security\Core\User\UserInterface;

interface AuthenticatedUserInterface extends UserInterface
{
    public function getId(): UserId;
}
