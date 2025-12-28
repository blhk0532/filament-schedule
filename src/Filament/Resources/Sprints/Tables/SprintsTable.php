<?php

namespace Adultdate\Schedule\Filament\Resources\Sprints\Tables;

use Adultdate\Schedule\Enums\Priority;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SprintsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Sprint')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),

                TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->formatStateUsing(fn (?Priority $state): ?string => $state?->getLabel())
                    ->color(function (?Priority $state): string {
                        return match ($state) {
                            Priority::Low => 'success',
                            Priority::Medium => 'info',
                            Priority::High => 'warning',
                            Priority::Urgent => 'danger',
                            null => 'gray',
                        };
                    })
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('Starts at')
                    ->date()
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Ends at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('priority')
                    ->options(collect(Priority::cases())
                        ->mapWithKeys(fn (Priority $priority) => [$priority->value => $priority->getLabel()])
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('starts_at', 'desc');
    }
}
