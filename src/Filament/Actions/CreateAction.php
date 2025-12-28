<?php

namespace Adultdate\Schedule\Filament\Actions;

use Filament\Schemas\Schema;
use Adultdate\Schedule\Concerns\CalendarAction;
use Adultdate\Schedule\Contracts\HasCalendar;

class CreateAction extends \Filament\Actions\CreateAction
{
    use CalendarAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->schema(
                fn (Schema $schema, CreateAction $action, HasCalendar $livewire) => $livewire
                    ->getFormSchemaForModel($schema, $action->getModel())
            )
            ->after(fn (HasCalendar $livewire) => $livewire->refreshRecords())
            ->cancelParentActions()
        ;
    }
}
