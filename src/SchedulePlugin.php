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
use Adultdate\Schedule\Filament\Pages\EventCalendarPage;
use Filament\Support\Concerns\EvaluatesClosures;
use Closure;
use Adultdate\Schedule\Filament\Widgets\FullCalendarWidget;

class SchedulePlugin implements Plugin
{

    use EvaluatesClosures;

    protected array $plugins = ['dayGrid', 'timeGrid', 'interaction', 'list', 'moment', 'momentTimezone'];

    protected ?string $schedulerLicenseKey = null;

    protected array $config = [];

    protected string | Closure | null $timezone = null;

    protected string | Closure | null $locale = null;

    protected ?bool $editable = null;

    protected ?bool $selectable = null;


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
                EventCalendarPage::class,
                Calendar::class,
                SchedulesCalendar::class,
                CalendarSettingsPage::class,
            ])
            ->resources([
                CalendarEventResource::class,
                MeetingResource::class,
                ScheduleResource::class,
                ProjectResource::class,
                SprintResource::class,
                TaskResource::class,
            ])
            ->widgets([
                SchedulesCalendarWidget::class,
                CalendarWidget::class,
            //    EventCalendar::class,
            //    FullCalendarWidget::class,
            ]);
    }

    public function boot(Panel $panel): void {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }
 
        public function plugins(array $plugins, bool $merge = true): static
    {
        $this->plugins = $merge ? array_merge($this->plugins, $plugins) : $plugins;

        return $this;
    }

    public function getPlugins(): array
    {
        return $this->plugins;
    }

    public function schedulerLicenseKey(string $schedulerLicenseKey): static
    {
        $this->schedulerLicenseKey = $schedulerLicenseKey;

        return $this;
    }

    public function getSchedulerLicenseKey(): ?string
    {
        return $this->schedulerLicenseKey;
    }

    public function config(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function timezone(string | Closure $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getTimezone(): string
    {
        return $this->evaluate($this->timezone) ?? config('app.timezone');
    }

    public function locale(string | Closure $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->evaluate($this->locale) ?? strtolower(str_replace('_', '-', app()->getLocale()));
    }

    public function editable(bool $editable = true): static
    {
        $this->editable = $editable;

        return $this;
    }

    public function isEditable(): bool
    {
        return $this->editable ?? data_get($this->config, 'editable', false);
    }

    public function selectable(bool $selectable = true): static
    {
        $this->selectable = $selectable;

        return $this;
    }

    public function isSelectable(): bool
    {
        // Enable date selection by default unless explicitly disabled in config
        return $this->selectable ?? data_get($this->config, 'selectable', true);
    }
}
