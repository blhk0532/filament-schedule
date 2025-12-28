<?php

namespace Adultdate\Schedule\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Task details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),

                        Select::make('project_id')
                            ->label('Project')
                            ->relationship('project', 'title')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        Select::make('user_id')
                            ->label('Assignee')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
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
                    ])
                    ->columns(2),
            ]);
    }
}
