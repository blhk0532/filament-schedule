<?php

namespace Adultdate\Schedule\Filament\Widgets\Concerns;

use function Adultdate\Schedule\array_merge_recursive_unique;

use Adultdate\Schedule\SchedulePlugin;

trait CanBeConfigured
{
    public function config(): array
    {
        return [];
    }

    protected function getConfig(): array
    {
        return array_merge_recursive_unique(
            SchedulePlugin::get()->getConfig(),
            $this->config(),
        );
    }
}
