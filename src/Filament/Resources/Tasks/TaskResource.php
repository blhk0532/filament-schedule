<?php

namespace Adultdate\Schedule\Filament\Resources\Tasks;

use Adultdate\Schedule\Filament\Resources\Tasks\Pages\CreateTask;
use Adultdate\Schedule\Filament\Resources\Tasks\Pages\EditTask;
use Adultdate\Schedule\Filament\Resources\Tasks\Pages\ListTasks;
use Adultdate\Schedule\Filament\Resources\Tasks\Schemas\TaskForm;
use Adultdate\Schedule\Filament\Resources\Tasks\Schemas\TaskInfolist;
use Adultdate\Schedule\Filament\Resources\Tasks\Tables\TasksTable;
use Adultdate\Schedule\Models\Task;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    public static function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TaskInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TasksTable::configure($table);
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
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }
}
