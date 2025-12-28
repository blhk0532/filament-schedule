<?php

namespace Adultdate\Schedule\Filament\Pages;

use Adultdate\Schedule\Filament\Widgets\SchedulesCalendarWidget;
use BackedEnum;
use Filament\Pages\Page as BasePage;
use UnitEnum;

class SchedulesCalendar extends BasePage
{
    protected string $view = 'adultdate-schedule::pages.schedules-calendar';

     protected static ?string $navigationLabel = 'Calendar';

     protected static ?int $sort = 3;

    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    /**
     * Return header widgets for the page so Filament will render them
     * in the page header area (the framework filters by canView()).
     *
     * @return array<class-string<\Filament\Widgets\Widget>>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            SchedulesCalendarWidget::class,
        ];
    }
}