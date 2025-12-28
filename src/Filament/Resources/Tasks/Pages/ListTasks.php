<?php

namespace Adultdate\Schedule\Filament\Resources\Tasks\Pages;

use Adultdate\Schedule\Filament\Resources\Sprints\SprintResource;
use Adultdate\Schedule\Filament\Resources\Tasks\TaskResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New task')
                ->icon('heroicon-o-plus'),
            Action::make('planSprint')
                ->label('New sprint')
                ->icon('heroicon-o-flag')
                ->color('primary')
                ->url(SprintResource::getUrl('create')),
        ];
    }
}
