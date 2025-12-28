<?php

namespace Adultdate\Schedule\Concerns;

trait CanRefreshCalendar
{
    public function refreshRecords(): static
    {
        // Use the FullCalendar-specific event name so the JS listener refetches events
        $this->dispatch('filament-fullcalendar--refresh');

        return $this;
    }

    public function refreshResources(): static
    {
        $this->setOption('resources', $this->getResourcesJs());

        return $this;
    }
}
