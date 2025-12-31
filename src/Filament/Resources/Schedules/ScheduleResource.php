<?php

namespace Adultdate\Schedule\Filament\Resources\Schedules;

use Adultdate\Schedule\Filament\Resources\Schedules\Pages\CreateSchedule;
use Adultdate\Schedule\Filament\Resources\Schedules\Pages\EditSchedule;
use Adultdate\Schedule\Filament\Resources\Schedules\Pages\ListSchedules;
use Adultdate\Schedule\Filament\Resources\Schedules\Schemas\ScheduleForm;
use Adultdate\Schedule\Filament\Resources\Schedules\Tables\SchedulesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;
use Adultdate\Schedule\Filament\Resources\Schedules\RelationManagers\PeriodsRelationManager;
use Adultdate\Schedule\Models\Schedule as ZapSchedule;

class ScheduleResource extends Resource
{
    protected static ?string $model = ZapSchedule::class;

    protected static ?int $sort = 3;

    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    public static function form(Schema $schema): Schema
    {
        return ScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchedulesTable::configure($table)
            ->modifyQueryUsing(fn ($query) => $query->with('periods'));
    }

    public static function getRelations(): array
    {
        return [
            PeriodsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchedules::route('/'),
            'create' => CreateSchedule::route('/create'),
            'edit' => EditSchedule::route('/{record}/edit'),
        ];
    }
}
