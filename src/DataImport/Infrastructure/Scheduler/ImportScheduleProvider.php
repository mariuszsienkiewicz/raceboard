<?php

declare(strict_types=1);

namespace App\DataImport\Infrastructure\Scheduler;

use App\DataImport\Application\Message\ImportRacesFromSource;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('import')]
class ImportScheduleProvider implements ScheduleProviderInterface
{
    public function __construct(
        private readonly CacheInterface $cache
    ) {

    }

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true)
            ->add(RecurringMessage::every('24 hours', new ImportRacesFromSource('maratony-polskie')))
            ->add(RecurringMessage::every('24 hours', new ImportRacesFromSource('running-life')));
    }
}
