<?php

namespace Adultdate\Schedule\Filament\Resources\Tasks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class TaskInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Title')
                    ->weight(FontWeight::SemiBold),

                TextEntry::make('project.title')
                    ->label('Project')
                    ->badge()
                    ->placeholder('—'),

                TextEntry::make('user.name')
                    ->label('Assignee')
                    ->badge()
                    ->placeholder('Unassigned'),

                TextEntry::make('starts_at')
                    ->label('Starts at')
                    ->dateTime(),

                TextEntry::make('ends_at')
                    ->label('Ends at')
                    ->dateTime(),

                TextEntry::make('description')
                    ->label('Description')
                    ->html()
                    ->columnSpanFull()
                    ->placeholder('No description provided')
                    ->hidden(fn ($record): bool => blank($record->description)),
            ]);
    }
}
