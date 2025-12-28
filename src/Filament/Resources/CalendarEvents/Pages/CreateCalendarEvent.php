<?php

declare(strict_types=1);

namespace Adultdate\Schedule\Filament\Resources\CalendarEvents\Pages;

use Adultdate\Schedule\Filament\Resources\CalendarEvents\CalendarEventResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

final class CreateCalendarEvent extends CreateRecord
{
    protected static string $resource = CalendarEventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set user_id to current user if not set (for non-admin users)
        if (! isset($data['user_id']) || ! Auth::user()?->hasRole('super_admin')) {
            $data['user_id'] = Auth::id();
        }

        return $data;
    }
}
