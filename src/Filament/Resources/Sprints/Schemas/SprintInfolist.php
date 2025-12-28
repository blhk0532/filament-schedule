<?php

namespace Adultdate\Schedule\Filament\Resources\Sprints\Schemas;

use Adultdate\Schedule\Enums\Priority;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class SprintInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Title')
                    ->weight(FontWeight::SemiBold),

                TextEntry::make('priority')
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
                    }),

                TextEntry::make('starts_at')
                    ->label('Starts at')
                    ->dateTime(),

                TextEntry::make('ends_at')
                    ->label('Ends at')
                    ->dateTime(),

                TextEntry::make('description')
                    ->label('Goals')
                    ->html()
                    ->columnSpanFull()
                    ->placeholder('No goals provided')
                    ->hidden(fn ($record): bool => blank($record->description)),
            ]);
    }
}
