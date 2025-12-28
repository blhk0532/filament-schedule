<?php

declare(strict_types=1);

namespace Adultdate\Schedule\Filament\Resources\CalendarEvents\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

final class CalendarEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event Details')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        DateTimePicker::make('start')
                            ->label('Start Date & Time')
                            ->required()
                            ->native(false)
                            ->seconds(false),

                        DateTimePicker::make('end')
                            ->label('End Date & Time')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->rule('after:start'),

                        Checkbox::make('all_day')
                            ->label('All Day Event'),

                        ColorPicker::make('background_color')
                            ->label('Background Color')
                            ->placeholder('Default'),

                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->default(fn () => Auth::id())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->visible(fn () => Auth::user()),
                    ])
                    ->columns(2),
            ]);
    }
}
