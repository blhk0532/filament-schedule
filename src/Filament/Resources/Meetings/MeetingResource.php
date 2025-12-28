<?php

namespace Adultdate\Schedule\Filament\Resources\Meetings;

use Adultdate\Schedule\Filament\Resources\Meetings\Pages\CreateMeeting;
use Adultdate\Schedule\Filament\Resources\Meetings\Pages\EditMeeting;
use Adultdate\Schedule\Filament\Resources\Meetings\Pages\ListMeetings;
use Adultdate\Schedule\Filament\Resources\Meetings\Schemas\MeetingForm;
use Adultdate\Schedule\Filament\Resources\Meetings\Schemas\MeetingInfolist;
use Adultdate\Schedule\Filament\Resources\Meetings\Tables\MeetingsTable;
use Adultdate\Schedule\Models\Meeting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class MeetingResource extends Resource
{
    protected static ?string $model = Meeting::class;

    protected static ?int $sort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    public static function form(Schema $schema): Schema
    {
        return MeetingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MeetingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MeetingsTable::configure($table);
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
            'index' => ListMeetings::route('/'),
            'create' => CreateMeeting::route('/create'),
            'edit' => EditMeeting::route('/{record}/edit'),
        ];
    }
}
