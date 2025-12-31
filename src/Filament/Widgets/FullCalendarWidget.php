<?php

namespace Adultdate\Schedule\Filament\Widgets;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Concerns\InteractsWithHeaderActions;
use Filament\Widgets\Widget;
use Adultdate\Schedule\SchedulePlugin;
use Adultdate\Schedule\Concerns\HasHeaderActions;
use Adultdate\Schedule\Filament\Widgets\Concerns\CanBeConfigured;
use Adultdate\Schedule\Filament\Widgets\Concerns\InteractsWithRawJS;

class FullCalendarWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;
    use HasHeaderActions, CanBeConfigured, InteractsWithRawJS;
    /**
     * Blade view used by this widget (NON-static in Filament v3)
     */
    protected string $view = 'adultdate-schedule::fullcalendar';
    protected static ?int $sort = 4;
    /**
     * Widget width
     */
    protected int | string | array $columnSpan = 'full';

    /**
     * Provide view data from the Schedule plugin
     */
    protected function getViewData(): array
    {
        /** @var SchedulePlugin|null $plugin */
        $plugin = filament()
            ->getCurrentPanel()
            ?->getPlugin('adultdate-schedule');

        return [
            // FullCalendar plugins
            'plugins' => $plugin?->getPlugins() ?? [
                'dayGrid',
                'timeGrid',
                'interaction',
                'list',
            ],

            // Localization
            'timezone' => $plugin?->getTimezone(),
            'locale'   => $plugin?->getLocale(),

            // Interaction flags
            'editable'   => $plugin?->isEditable() ?? false,
            'selectable' => $plugin?->isSelectable() ?? false,

            // Scheduler license key (optional)
            'schedulerLicenseKey' => $plugin?->getSchedulerLicenseKey(),

            // Additional FullCalendar configuration
            'config' => $plugin?->getConfig() ?? [],
        ];
    }

    /**
     * Provide a default fetchEvents method so Livewire calls will always exist
     * on the base widget. Specific calendar widgets may override this.
     */
    public function fetchEvents(array $info): array
    {
        return [];
    }

    protected function headerActions(): array
    {
        return [
            \Filament\Actions\Action::make('create'),
        ];
    }

    protected function modalActions(): array
    {
        return [
            \Filament\Actions\Action::make('edit'),
            \Filament\Actions\Action::make('delete'),
        ];
    }

    protected function viewAction(): \Filament\Actions\Action
    {
        // Use a non-colliding action name so it doesn't overwrite the widget's `$view` property
        return \Filament\Actions\Action::make('viewEvent');
    }
}
