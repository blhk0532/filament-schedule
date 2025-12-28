<?php

namespace Adultdate\Schedule\Filament\Resources\Meetings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MeetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Meeting details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),

                        RichEditor::make('description')
                            ->label('Agenda')
                            ->columnSpanFull(),

                        DateTimePicker::make('starts_at')
                            ->native(false)
                            ->seconds(false)
                            ->required(),

                        DateTimePicker::make('ends_at')
                            ->native(false)
                            ->seconds(false)
                            ->required()
                            ->rule('after:starts_at'),

                        Select::make('users')
                            ->label('Participants')
                            ->relationship('users', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
