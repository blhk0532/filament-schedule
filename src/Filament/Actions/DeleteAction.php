<?php

namespace Adultdate\Schedule\Filament\Actions;

use Adultdate\Schedule\Concerns\CalendarAction;
use Adultdate\Schedule\Contracts\HasCalendar;
use Illuminate\Database\Eloquent\Model;

class DeleteAction extends \Filament\Actions\DeleteAction
{
    use CalendarAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model(fn (HasCalendar $livewire) => $livewire->getEventModel());
        $this->record(fn (HasCalendar $livewire) => $livewire->getEventRecord());

        $this->before(function (HasCalendar $livewire) {
            if (! $livewire->getEventRecord()) {
                $livewire->refreshRecords();
                return false; // Prevent the action
            }
            return true;
        });

        $this->after(function (HasCalendar $livewire) {
            $livewire->eventRecord = null;
            $livewire->refreshRecords();
        });

        $this->hidden(static function (?Model $record): bool {
            if (! $record) {
                return false;
            }

            if (! method_exists($record, 'trashed')) {
                return false;
            }

            return $record->trashed();
        });

        $this->cancelParentActions();
    }
}
