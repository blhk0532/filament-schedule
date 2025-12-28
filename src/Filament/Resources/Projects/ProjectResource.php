<?php

namespace Adultdate\Schedule\Filament\Resources\Projects;

use Adultdate\Schedule\Filament\Resources\Projects\Pages\CreateProject;
use Adultdate\Schedule\Filament\Resources\Projects\Pages\EditProject;
use Adultdate\Schedule\Filament\Resources\Projects\Pages\ListProjects;
use Adultdate\Schedule\Filament\Resources\Projects\Schemas\ProjectForm;
use Adultdate\Schedule\Filament\Resources\Projects\Tables\ProjectsTable;
use Adultdate\Schedule\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|UnitEnum|null $navigationGroup = 'Schedules';

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
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
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}
