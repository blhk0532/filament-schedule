<?php

namespace Adultdate\Schedule\Filament\Resources\Meetings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class MeetingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Title')
                    ->weight(FontWeight::SemiBold),

                TextEntry::make('starts_at')
                    ->label('Starts at')
                    ->dateTime(),

                TextEntry::make('ends_at')
                    ->label('Ends at')
                    ->dateTime(),

                TextEntry::make('users.name')
                    ->label('Participants')
                    ->badge()
                    ->placeholder('No participants yet'),

                TextEntry::make('description')
                    ->label('Agenda')
                    ->html()
                    ->columnSpanFull()
                    ->placeholder('No agenda provided')
                    ->hidden(fn ($record): bool => blank($record->description)),
            ]);
    }
}
