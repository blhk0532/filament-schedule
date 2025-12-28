<?php

namespace Adultdate\Schedule\Concerns;

use Adultdate\Schedule\Contracts\ContextualInfo;
use Adultdate\Schedule\Contracts\HasCalendar;
use Adultdate\Schedule\ValueObjects\DateClickInfo;
use Adultdate\Schedule\ValueObjects\DateSelectInfo;
use Adultdate\Schedule\ValueObjects\EventClickInfo;
use Adultdate\Schedule\ValueObjects\NoEventsClickInfo;

trait CalendarAction
{
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        /** @var InteractsWithCalendar $livewire */
        $livewire = $this->getLivewire();

        // Action is used outside the calendar
        if (! ($livewire instanceof HasCalendar)) {
            return parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType);
        }

        return match ($parameterType) {
            DateClickInfo::class, DateSelectInfo::class, EventClickInfo::class, NoEventsClickInfo::class, ContextualInfo::class => [
                $livewire->getCalendarContextInfo(),
            ],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }
}
