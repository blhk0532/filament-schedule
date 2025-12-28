<?php

declare(strict_types=1);

namespace Adultdate\Schedule\Filament\Resources\CalendarEvents;

use Adultdate\Schedule\Filament\Resources\CalendarEvents\Pages\CreateCalendarEvent;
use Adultdate\Schedule\Filament\Resources\CalendarEvents\Pages\EditCalendarEvent;
use Adultdate\Schedule\Filament\Resources\CalendarEvents\Pages\ListCalendarEvents;
use Adultdate\Schedule\Filament\Resources\CalendarEvents\Pages\ViewCalendarEvent;
use Adultdate\Schedule\Filament\Resources\CalendarEvents\Schemas\CalendarEventForm;
use Adultdate\Schedule\Filament\Resources\CalendarEvents\Schemas\CalendarEventInfolist;
use Adultdate\Schedule\Filament\Resources\CalendarEvents\Tables\CalendarEventsTable;
use Adultdate\Schedule\Models\CalendarEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class CalendarEventResource extends Resource
{
    protected static ?string $model = CalendarEvent::class;

    protected static ?int $sort = 1;

    protected static ?string $navigationLabel = 'Events';

    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    public static function form(Schema $schema): Schema
    {
        return CalendarEventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CalendarEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CalendarEventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCalendarEvents::route('/'),
            'create' => CreateCalendarEvent::route('/create'),
            'view' => ViewCalendarEvent::route('/{record}'),
            'edit' => EditCalendarEvent::route('/{record}/edit'),
        ];
    }
}
