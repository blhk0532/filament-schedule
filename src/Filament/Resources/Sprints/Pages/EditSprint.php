<?php

namespace Adultdate\Schedule\Filament\Resources\Sprints\Pages;

use Adultdate\Schedule\Filament\Resources\Sprints\SprintResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSprint extends EditRecord
{
    protected static string $resource = SprintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
