<?php

namespace Adultdate\Schedule\Filament;

use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;
use Adultdate\Schedule\Concerns\InteractsWithCalendar;
use Adultdate\Schedule\Contracts\HasCalendar;

abstract class CalendarWidget extends Widget implements HasActions, HasCalendar, HasSchemas
{
    use InteractsWithCalendar;

    protected string $view = 'adultdate-schedule::calendar-widget';

    protected int | string | array $columnSpan = 'full';

    public function eventAssetUrl(): string
    {
        return \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('calendar-event', 'adultdate-schedule');
    }
}
