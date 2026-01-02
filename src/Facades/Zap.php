<?php

namespace Adultdate\Schedule\Facades;

use Adultdate\Schedule\Builders\ScheduleBuilder;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ScheduleBuilder for(mixed $schedulable)
 * @method static ScheduleBuilder schedule()
 * @method static array findConflicts(\Adultdate\Schedule\Models\Schedule $schedule)
 * @method static bool hasConflicts(\Adultdate\Schedule\Models\Schedule $schedule)
 *
 * @see \Adultdate\Schedule\Services\ScheduleService
 */
class Zap extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'zap';
    }
}
