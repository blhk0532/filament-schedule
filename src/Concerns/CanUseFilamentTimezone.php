<?php

namespace Adultdate\Schedule\Concerns;

trait CanUseFilamentTimezone
{
    protected bool $useFilamentTimezone = false;

    public function shouldUseFilamentTimezone(): bool
    {
        return $this->useFilamentTimezone;
    }
}
