<?php

namespace Adultdate\Schedule\Filament\Resources\Projects\Pages;

use Adultdate\Schedule\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New project')
                ->icon('heroicon-o-rectangle-stack'),
        ];
    }
}
