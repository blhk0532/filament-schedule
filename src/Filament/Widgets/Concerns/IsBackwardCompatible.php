<?php

namespace Adultdate\Schedule\Filament\Widgets\Concerns;

trait IsBackwardCompatible
{
    protected function getHeaderActions(): array
    {
        return $this->headerActions();
    }

    protected function getFormActions(): array
    {
        return $this->modalActions();
    }
}
