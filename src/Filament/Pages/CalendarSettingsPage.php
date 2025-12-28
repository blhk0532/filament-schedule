<?php

declare(strict_types=1);

namespace Adultdate\Schedule\Filament\Pages;

use Adultdate\Schedule\Models\CalendarSettings;
use BackedEnum;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

final class CalendarSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected string $view = 'adultdate-schedule::pages.calendar-settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $sort = 10;

    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    public function mount(): void
    {
        $settings = CalendarSettings::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'opening_hour_start' => '09:00',
                'opening_hour_end' => '17:00',
            ]
        );

        $this->form->fill([
            'opening_hour_start' => $settings->opening_hour_start?->format('H:i') ?? '09:00',
            'opening_hour_end' => $settings->opening_hour_end?->format('H:i') ?? '17:00',
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        CalendarSettings::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'opening_hour_start' => $data['opening_hour_start'],
                'opening_hour_end' => $data['opening_hour_end'],
            ]
        );

        Notification::make()
            ->title('Inställningar sparade')
            ->success()
            ->send();
    }

    protected function getFormSchema(): array
    {
        return [
            TimePicker::make('opening_hour_start')
                ->label('Öppettid Start')
                ->required()
                ->native(false)
                ->seconds(false)
                ->default('09:00'),

            TimePicker::make('opening_hour_end')
                ->label('Öppettid Slut')
                ->required()
                ->native(false)
                ->seconds(false)
                ->default('17:00')
                ->rule('after:opening_hour_start'),
        ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }
}
