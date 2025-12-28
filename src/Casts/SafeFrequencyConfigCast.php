<?php

namespace Adultdate\Schedule\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Adultdate\Schedule\Data\FrequencyConfig;
use Adultdate\Schedule\Models\Schedule;

class SafeFrequencyConfigCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        /** @var Schedule $schedule */
        $schedule = $model;
        $configArray = json_decode($value, true);

        $frequency = $schedule->frequency;

        if (! $frequency || $configArray === null) {
            return null;
        }

        if (is_string($frequency)) {
            return $configArray;
        }

        // Support monthly weekday-style configs that specify weekdays (not supported by MonthlyFrequencyConfig)
        if ($frequency->value === 'monthly' && isset($configArray['monthly_style']) && $configArray['monthly_style'] === 'weekday') {
            return $configArray;
        }

        return $frequency->configClass()::fromArray($configArray);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        /** @var FrequencyConfig|array|null $config */
        $config = $value;

        if ($config === null) {
            return null;
        }

        if (is_array($config)) {
            return json_encode($config);
        }

        return json_encode($config->toArray());
    }
}
