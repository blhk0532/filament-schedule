<?php

namespace Adultdate\Schedule\Filament\Resources\Sprints;

use Adultdate\Schedule\Filament\Resources\Sprints\Pages\CreateSprint;
use Adultdate\Schedule\Filament\Resources\Sprints\Pages\EditSprint;
use Adultdate\Schedule\Filament\Resources\Sprints\Pages\ListSprints;
use Adultdate\Schedule\Filament\Resources\Sprints\Schemas\SprintForm;
use Adultdate\Schedule\Filament\Resources\Sprints\Schemas\SprintInfolist;
use Adultdate\Schedule\Filament\Resources\Sprints\Tables\SprintsTable;
use Adultdate\Schedule\Models\Sprint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SprintResource extends Resource
{
    protected static ?string $model = Sprint::class;

    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    public static function form(Schema $schema): Schema
    {
        return SprintForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SprintInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SprintsTable::configure($table);
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
            'index' => ListSprints::route('/'),
            'create' => CreateSprint::route('/create'),
            'edit' => EditSprint::route('/{record}/edit'),
        ];
    }
}
