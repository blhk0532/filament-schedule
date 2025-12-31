<?php

namespace Adultdate\Schedule;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ScheduleServiceProvider extends PackageServiceProvider
{

    public static string $name = 'adultdate-schedule';

    public static string $viewNamespace = 'adultdate-schedule';

    public function configurePackage(Package $package): void
    {
        $package
            ->name('adultdate-schedule')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews('adultdate-schedule')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations();
            });
    }
 
    public function packageBooted(): void
    {
        /* 
         * Publish migrations
         */
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'adultdate-schedule-migrations');

        /*
         * Publish frontend assets (from /dist)
         */
        $this->publishes([
            __DIR__ . '/../dist/js' => public_path('vendor/adultdate-schedule'),
        ], 'adultdate-schedule-assets');


        $this->publishes([
            __DIR__ . '/../resources/dist' => public_path('vendor/adultdate-schedule'),
        ], 'adultdate-schedule-assets');

        /*
         * Register Filament assets (guarded)
         */
        $distPath = __DIR__ . '/../dist/js';

        if (is_dir($distPath)) {
            FilamentAsset::register(
                assets: [
                    AlpineComponent::make(
                        'calendar',
                        $distPath . '/calendar.js'
                    ),
                    AlpineComponent::make(
                        'calendar-context-menu',
                        $distPath . '/calendar-context-menu.js'
                    ),
                    AlpineComponent::make(
                        'calendar-event',
                        $distPath . '/calendar-event.js'
                    ),
                     AlpineComponent::make(
                        'filament-fullcalendar-alpine', 
                        $distPath . '/filament-fullcalendar.js'
                    ),
                    Css::make(
                        'event-calendar-styles',
                        'https://cdn.jsdelivr.net/npm/@event-calendar/build@4.5.0/dist/event-calendar.min.css'
                    ),
                    Js::make(
                        'event-calendar-script',
                        'https://cdn.jsdelivr.net/npm/@event-calendar/build@4.5.0/dist/event-calendar.min.js'
                    ),
                ],
                package: 'adultdate-schedule'
            );
        }

        // Ensure Livewire can resolve package widgets by their dotted name (e.g. adultdate.schedule.filament.widgets.calendar-widget)
        if (class_exists('\Livewire\Livewire')) {
            // Register explicit aliases that match the dotted names Livewire expects
            \Livewire\Livewire::component('adultdate.schedule.filament.widgets.calendar-widget', \Adultdate\Schedule\Filament\Widgets\CalendarWidget::class);
            \Livewire\Livewire::component('adultdate.schedule.filament.widgets.schedules-calendar-widget', \Adultdate\Schedule\Filament\Widgets\SchedulesCalendarWidget::class);
            \Livewire\Livewire::component('adultdate.schedule.filament.widgets.full-calendar-widget', \Adultdate\Schedule\Filament\Widgets\FullCalendarWidget::class);
            \Livewire\Livewire::component('adultdate.schedule.filament.widgets.event-calendar', \Adultdate\Schedule\Filament\Widgets\EventCalendar::class);
        }
    }
}
