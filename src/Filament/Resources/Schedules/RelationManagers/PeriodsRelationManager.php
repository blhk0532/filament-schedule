<?php

namespace Adultdate\Schedule\Filament\Resources\Schedules\RelationManagers;

use Adultdate\Schedule\Filament\Resources\Schedules\ScheduleResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Adultdate\Schedule\Models\SchedulePeriod;

class PeriodsRelationManager extends RelationManager
{
    protected static string $relationship = 'periods';

    protected static ?string $recordTitleAttribute = 'date';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')->required(),
                TextInput::make('start_time')->label('Start time')->placeholder('HH:MM')->required(),
                TextInput::make('end_time')->label('End time')->placeholder('HH:MM')->required(),
                Toggle::make('is_available')->label('Available')->default(true),
                TextInput::make('metadata')->label('Metadata')->helperText('JSON encoded metadata')->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('date')->date()->sortable(),
                TextColumn::make('start_time'),
                TextColumn::make('end_time'),
                IconColumn::make('is_available')->boolean(),
            ])
            ->defaultSort('date', 'desc');
    }
}
