<?php

declare(strict_types=1);

namespace Adultdate\Schedule\Filament\Resources\CalendarEvents\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

final class CalendarEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event Details')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Title')
                            ->weight(FontWeight::SemiBold)
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('No description provided')
                            ->hidden(fn ($record): bool => blank($record->description)),

                        TextEntry::make('start')
                            ->label('Start Date & Time')
                            ->dateTime('M d, Y H:i'),

                        TextEntry::make('end')
                            ->label('End Date & Time')
                            ->dateTime('M d, Y H:i'),

                        IconEntry::make('all_day')
                            ->label('All Day Event')
                            ->boolean(),

                        TextEntry::make('background_color')
                            ->label('Background Color')
                            ->color(fn ($state) => $state)
                            ->badge()
                            ->placeholder('Default'),

                        TextEntry::make('user.name')
                            ->label('User')
                            ->badge()
                            ->placeholder('—'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('M d, Y H:i')
                            ->toggleable(isToggledHiddenByDefault: true),

                        TextEntry::make('updated_at')
                            ->label('Updated')
                            ->dateTime('M d, Y H:i')
                            ->toggleable(isToggledHiddenByDefault: true),
                    ])
                    ->columns(2),
            ]);
    }
}
