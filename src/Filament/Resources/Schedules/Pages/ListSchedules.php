<?php

namespace Adultdate\Schedule\Filament\Resources\Schedules\Pages;

use Adultdate\Schedule\Filament\Resources\Schedules\ScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New schedule')
                ->icon('heroicon-o-calendar'),
        ];
    }
}
