<?php

namespace Adultdate\Schedule\Filament\Pages;

use Adultdate\Schedule\Filament\Widgets\EventCalendar;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Widgets\Widget;
use UnitEnum;

class EventCalendarPage extends Page
{
    protected string $view = 'adultdate-schedule::pages.calendar';

    protected static ?string $navigationLabel = 'Booking';

    protected static ?int $sort = 1;

    protected static string | UnitEnum | null $navigationGroup = 'Schedules';

    /**
     * Return widgets for the page so Filament will render them
     * in the page content area (the framework filters by canView()).
     *
     * @return array<class-string<Widget>>
     */
    protected function getWidgets(): array
    {
        return [
            EventCalendar::class,
        ];
    }

    /**
     * Return header widgets for the page so Filament will render them
     * in the page header area (the framework filters by canView()).
     *
     * @return array<class-string<Widget>>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            EventCalendar::class,
        ];
    }
}
