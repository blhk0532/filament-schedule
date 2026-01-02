<?php

namespace Adultdate\Schedule\Filament\Widgets\Concerns;

use Adultdate\Schedule\SchedulePlugin;

use function Adultdate\Schedule\array_merge_recursive_unique;

trait CanBeConfigured
{
    public function config(): array
    {
        return [];
    }

    public function getConfig(): array
    {
        return array_merge_recursive_unique(
            SchedulePlugin::get()->getConfig(),
            $this->config(),
        );
    }

    public function getOptions(): array
    {
        return $this->getConfig();
    }
}
