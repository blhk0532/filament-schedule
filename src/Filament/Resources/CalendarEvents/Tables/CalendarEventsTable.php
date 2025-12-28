<?php

declare(strict_types=1);

namespace Adultdate\Schedule\Filament\Resources\CalendarEvents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class CalendarEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn ($record) => $record->description ? Str::limit($record->description, 50) : null),

                TextColumn::make('start')
                    ->label('Start')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                TextColumn::make('end')
                    ->label('End')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                IconColumn::make('all_day')
                    ->label('All Day')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('background_color')
                    ->label('Color')
                    ->color(fn ($state) => $state)
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('all_day')
                    ->label('All Day Events'),
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
            ->defaultSort('start', 'desc')
            ->modifyQueryUsing(function ($query) {
                // Show only current user's events unless admin
            //    if (! Auth::user()?->hasRole('super_admin')) {
            //        $query->where('user_id', Auth::id());
            //    }
            });
    }
}
