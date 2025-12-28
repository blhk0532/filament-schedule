<?php

namespace Adultdate\Schedule\ValueObjects;

use Adultdate\Schedule\Contracts\ContextualInfo;
use Adultdate\Schedule\Enums\Context;

readonly class NoEventsClickInfo implements ContextualInfo
{
    public CalendarView $view;

    protected array $originalData;

    public function __construct(array $data, bool $useFilamentTimezone)
    {
        $this->originalData = $data;

        $this->view = new CalendarView(
            data_get($data, 'view'),
            data_get($data, 'tzOffset'),
            $useFilamentTimezone
        );
    }

    public function getContext(): Context
    {
        return Context::NoEventsClick;
    }
}
