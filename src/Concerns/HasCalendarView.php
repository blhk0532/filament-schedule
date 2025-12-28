<?php

namespace Adultdate\Schedule\Concerns;

use Adultdate\Schedule\Enums\CalendarViewType;

trait HasCalendarView
{
    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;

    public function getCalendarView(): CalendarViewType
    {
        return $this->calendarView;
    }
}
