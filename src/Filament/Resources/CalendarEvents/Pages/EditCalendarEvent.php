<?php

declare(strict_types=1);

namespace Adultdate\Schedule\Filament\Resources\CalendarEvents\Pages;

use Adultdate\Schedule\Filament\Resources\CalendarEvents\CalendarEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

final class EditCalendarEvent extends EditRecord
{
    protected static string $resource = CalendarEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure user_id is preserved for non-admin users
        if (! Auth::user()?->hasRole('super_admin')) {
            $data['user_id'] = $this->record->user_id;
        }

        return $data;
    }
}
