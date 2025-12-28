<?php

namespace Adultdate\Schedule\Contracts;

use Adultdate\Schedule\ValueObjects\CalendarEvent;

interface Eventable
{
    public function toCalendarEvent(): CalendarEvent;
}
