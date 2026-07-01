<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Email;

use App\RaceCatalog\Domain\Model\Race;
use App\UserProfile\Domain\Model\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class WatchlistMailer
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    /**
     * @param list<Race> $races
     */
    public function sendNewRacesDigest(User $user, array $races): void
    {
        $raceList = implode("\n", array_map(
            fn (Race $r) => sprintf('- %s (%s)', $r->getName(), $r->getCity()),
            $races,
        ));

        $email = (new Email())
            ->to($user->getEmail())
            ->subject(sprintf('%d new races you might like', count($races)))
            ->text(sprintf(
                "Hi %s,\n\nNew races in cities you follow:\n\n%s\n\nCheck them out on RaceBoard!",
                $user->getDisplayName(),
                $raceList,
            ));

        $this->mailer->send($email);
    }
}
