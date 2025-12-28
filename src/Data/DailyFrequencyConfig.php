<?php

namespace Adultdate\Schedule\Data;

use Adultdate\Schedule\Models\Schedule;

class DailyFrequencyConfig extends FrequencyConfig
{
    public static function fromArray(array $data): \Adultdate\Schedule\Data\FrequencyConfig
    {
        return new self;
    }

    public function getNextRecurrence(\Carbon\CarbonInterface $current): \Carbon\CarbonInterface
    {
        return $current->copy()->addDay();
    }

    public function shouldCreateInstance(\Carbon\CarbonInterface $date): bool
    {
        return true;
    }

    public function shouldCreateRecurringInstance(Schedule $schedule, \Carbon\CarbonInterface $date): bool
    {
        return true;
    }
}
