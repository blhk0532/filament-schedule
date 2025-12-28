<?php

namespace Adultdate\Schedule;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Navigation\NavigationGroup;
use Adultdate\Schedule\Filament\Pages\Calendar;
use Adultdate\Schedule\Filament\Resources\Schedules\ScheduleResource;
use Adultdate\Schedule\Filament\Widgets\SchedulesCalendarWidget;
use Adultdate\Schedule\Filament\Pages\CalendarSettingsPage;
use Adultdate\Schedule\Filament\Widgets\CalendarWidget;
use Adultdate\Schedule\Filament\Widgets\EventCalendar;
use Adultdate\Schedule\Filament\Resources\CalendarEvents\CalendarEventResource;
use Adultdate\Schedule\Filament\Resources\Meetings\MeetingResource;
use Adultdate\Schedule\Filament\Resources\Projects\ProjectResource;
use Adultdate\Schedule\Filament\Resources\Sprints\SprintResource;
use Adultdate\Schedule\Filament\Resources\Tasks\TaskResource;
use Adultdate\Schedule\Filament\Pages\SchedulesCalendar;

class SchedulePlugin implements Plugin
{
    public function getId(): string
    {
        return 'adultdate-schedule';
    }

    public function register(Panel $panel): void {
        
        $panel
            ->navigationGroups([
                NavigationGroup::make('Schedules')
                    ->icon('heroicon-o-calendar-days'),
            ])
            ->pages([
                Calendar::class,
                CalendarSettingsPage::class,
                SchedulesCalendar::class,
            ])
            ->resources([
                ScheduleResource::class,
                CalendarEventResource::class,
                MeetingResource::class,
                ProjectResource::class,
                SprintResource::class,
                TaskResource::class,
            ])
            ->widgets([
              SchedulesCalendarWidget::class,
                CalendarWidget::class,
                EventCalendar::class,
            ]);
    }

    public function boot(Panel $panel): void {

    }

    public static function make(): static
    {
        return app(static::class);
    }
}
