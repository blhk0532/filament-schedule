<?php

namespace Adultdate\Schedule\Filament\Resources\Schedules\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language as CodeLanguage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Adultdate\Schedule\Enums\Frequency;
use Adultdate\Schedule\Enums\ScheduleTypes;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),

                Select::make('schedule_type')
                    ->label('Type')
                    ->options(collect(ScheduleTypes::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])->toArray())
                    ->required(),

                DatePicker::make('start_date')
                    ->label('Start date')
                    ->required(),

                DatePicker::make('end_date')
                    ->label('End date'),

                Select::make('frequency')
                    ->label('Recurrence')
                    ->options(collect(Frequency::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])->toArray())
                    ->reactive()
                    ->nullable(),

                CheckboxList::make('frequency_config.days')
                    ->label('Week days')
                    ->options([
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                    ])
                    ->visible(fn ($get) => in_array($get('frequency'), array_map(fn ($c) => $c->value, Frequency::filteredByWeekday()))),

                Select::make('frequency_config.monthly_style')
                    ->label('Monthly style')
                    ->options([
                        'day_of_month' => 'Day(s) of month',
                        'weekday' => 'Weekday of month',
                    ])
                    ->visible(fn ($get) => $get('frequency') === 'monthly')
                    ->reactive(),

                Select::make('frequency_config.days_of_month')
                    ->label('Days of month')
                    ->multiple()
                    ->options(array_combine(range(1, 31), range(1, 31)))
                    ->helperText('Select one or more days of the month (1-31). Use 31 for the last day when applicable.')
                    ->visible(fn ($get) => in_array($get('frequency'), array_map(fn ($c) => $c->value, Frequency::filteredByDaysOfMonth())))
                    ->rules(['array', 'nullable']),

                CodeEditor::make('metadata')
                    ->label('Metadata')
                    ->language(CodeLanguage::Json)
                    ->helperText('Valid JSON object; will be stored as array')
                    ->rules(['nullable', 'json'])
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : null)
                    ->dehydrateStateUsing(fn ($state) => $state ? json_decode($state, true) : null),
            ]);
    }
}
