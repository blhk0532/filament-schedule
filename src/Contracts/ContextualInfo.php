<?php

namespace Adultdate\Schedule\Contracts;

use Adultdate\Schedule\Enums\Context;

interface ContextualInfo
{
    public function getContext(): Context;
}
