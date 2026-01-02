<?php

namespace Adultdate\Schedule\Filament\Pages;

use Adultdate\Schedule\Filament\Widgets\CalendarWidget;
use Filament\Pages\Page;
use Filament\Widgets\Widget;
use UnitEnum;

class Calendar extends Page
{
    protected string $view = 'adultdate-schedule::pages.calendar';

    protected static ?string $navigationLabel = 'Kalender';

    protected static ?int $sort = 2;

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
            CalendarWidget::class,
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
            CalendarWidget::class,
        ];
    }
}
