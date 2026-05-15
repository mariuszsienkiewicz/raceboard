<?php

declare(strict_types=1);

namespace App\UserProfile\Infrastructure\Persistence\Doctrine\Type;

use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractIdType;
use App\UserProfile\Domain\Model\WatchlistEntryId;

final class WatchlistEntryIdType extends AbstractIdType
{
    public function getName(): string
    {
        return 'watchlist_entry_id';
    }

    protected function getIdClass(): string
    {
        return WatchlistEntryId::class;
    }
}
