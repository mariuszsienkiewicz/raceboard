<?php

declare(strict_types=1);

namespace App\DataImport\Infrastructure\Scheduler;

use App\DataImport\Application\Message\ImportRacesFromSource;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('import')]
class ImportScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(RecurringMessage::every('24 hours', new ImportRacesFromSource('maratony-polskie')))
            ->add(RecurringMessage::every('24 hours', new ImportRacesFromSource('running-life')));
    }
}
