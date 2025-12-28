<?php

namespace Adultdate\Schedule\Filament\Resources\Sprints\Pages;

use Adultdate\Schedule\Filament\Resources\Sprints\SprintResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSprints extends ListRecords
{
    protected static string $resource = SprintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New sprint')
                ->icon('heroicon-o-flag'),
        ];
    }
}
