<?php

namespace Adultdate\Schedule\Filament\Resources\Meetings\Pages;

use Adultdate\Schedule\Filament\Resources\Meetings\MeetingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMeetings extends ListRecords
{
    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New meeting')
                ->icon('heroicon-o-calendar'),
        ];
    }
}
