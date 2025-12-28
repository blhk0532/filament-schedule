<?php

namespace Adultdate\Schedule\Enums;

use Carbon\CarbonInterface;
use Adultdate\Schedule\Data\FrequencyConfig;

enum Frequency: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case BIWEEKLY = 'biweekly';
    case MONTHLY = 'monthly';
    case BIMONTHLY = 'bimonthly';
    case QUARTERLY = 'quarterly';
    case SEMIANNUALLY = 'semiannually';
    case ANNUALLY = 'annually';

    public function getNextRecurrence(CarbonInterface $current): CarbonInterface
    {
        return match ($this) {
            self::DAILY => $current->copy()->addDay(),
            self::WEEKLY => $current->copy()->addWeek(),
            self::BIWEEKLY => $current->copy()->addWeeks(2),
            self::MONTHLY => $current->copy()->addMonth(),
            self::BIMONTHLY => $current->copy()->addMonths(2),
            self::QUARTERLY => $current->copy()->addMonths(3),
            self::SEMIANNUALLY => $current->copy()->addMonths(6),
            self::ANNUALLY => $current->copy()->addYear(),
        };
    }

    /**
     * @return class-string<FrequencyConfig>
     */
    public function configClass(): string
    {
        return match ($this) {
            self::DAILY => \Adultdate\Schedule\Data\DailyFrequencyConfig::class,
            self::WEEKLY => \Adultdate\Schedule\Data\WeeklyFrequencyConfig::class,
            self::BIWEEKLY => \Adultdate\Schedule\Data\BiWeeklyFrequencyConfig::class,
            self::MONTHLY => \Adultdate\Schedule\Data\MonthlyFrequencyConfig::class,
            self::BIMONTHLY => \Adultdate\Schedule\Data\BiMonthlyFrequencyConfig::class,
            self::QUARTERLY => \Adultdate\Schedule\Data\QuarterlyFrequencyConfig::class,
            self::SEMIANNUALLY => \Adultdate\Schedule\Data\SemiAnnuallyFrequencyConfig::class,
            self::ANNUALLY => \Adultdate\Schedule\Data\AnnuallyFrequencyConfig::class,
        };
    }

    public static function filteredByWeekday(): array
    {
        return [
            self::WEEKLY,
            self::BIWEEKLY,
        ];
    }

    public static function filteredByDaysOfMonth(): array
    {
        return [
            self::MONTHLY,
            self::BIMONTHLY,
            self::QUARTERLY,
            self::SEMIANNUALLY,
            self::ANNUALLY,
        ];
    }
}
