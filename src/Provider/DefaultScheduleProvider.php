<?php

declare(strict_types=1);

namespace App\Provider;

use App\Message\ExportCleanupMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('default')]
class DefaultScheduleProvider implements ScheduleProviderInterface {
    public function getSchedule() : Schedule {
        // cleanup old exports every hour on the hour
        return (new Schedule())->add(
            RecurringMessage::every('1 hour', new ExportCleanupMessage(), from: '1:00')
        );
    }
}
