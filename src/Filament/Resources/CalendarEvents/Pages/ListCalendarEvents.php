<?php

declare(strict_types=1);

namespace Adultdate\Schedule\Filament\Resources\CalendarEvents\Pages;

use Adultdate\Schedule\Filament\Resources\CalendarEvents\CalendarEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListCalendarEvents extends ListRecords
{
    protected static string $resource = CalendarEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
