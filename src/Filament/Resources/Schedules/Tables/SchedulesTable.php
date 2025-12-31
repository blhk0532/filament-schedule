<?php

namespace Adultdate\Schedule\Filament\Resources\Schedules\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('name')->searchable()->wrap(),
                TextColumn::make('schedule_type')->label('Type')->sortable(),
                TextColumn::make('frequency')->label('Recurrence')->sortable()->formatStateUsing(function ($state, $record) {
                    if (! $state) {
                        return '-';
                    }

                    $details = ucfirst($state->value);

                    if ($state->value === 'monthly' && ($record->frequency_config->days_of_month ?? null)) {
                        $days = implode(',', $record->frequency_config->days_of_month);
                        $details .= " (days: {$days})";
                    }

                    if (($record->frequency_config->days ?? null) && is_array($record->frequency_config->days)) {
                        $days = implode(',', $record->frequency_config->days);
                        $details .= " (weekdays: {$days})";
                    }

                    return $details;
                }),
                IconColumn::make('is_active')->label('Active')->boolean()->sortable(),
                TextColumn::make('start_date')->label('Start')->date()->sortable(),
                TextColumn::make('end_date')->label('End')->date()->sortable(),
                TextColumn::make('total_duration')->label('Total (min)')->sortable(),
            ])
            ->defaultSort('id', 'desc');
    }
}
