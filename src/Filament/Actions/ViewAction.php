<?php

namespace Adultdate\Schedule\Filament\Actions;

use Filament\Schemas\Schema;
use Adultdate\Schedule\Contracts\HasCalendar;

class ViewAction extends \Filament\Actions\ViewAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->model(fn (HasCalendar $livewire) => $livewire->getEventModel())
            ->record(fn (HasCalendar $livewire) => $livewire->getEventRecord())
            ->before(function (HasCalendar $livewire) {
                if (! $livewire->getEventRecord()) {
                    $livewire->refreshRecords();
                    return false; // Prevent the action
                }
                return true;
            })
            ->schema(
                fn (ViewAction $action, Schema $schema, HasCalendar $livewire) => $livewire
                    ->getInfolistSchemaForModel($schema, $action->getModel())
                    ->record($livewire->getEventRecord()),
            )
            ->cancelParentActions()
        ;
    }
}
