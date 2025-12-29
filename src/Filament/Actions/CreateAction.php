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
            ->mutateFormDataUsing(function (array $data): array {
                $model = $this->getModel();
                if ($model && is_a($model, \Adultdate\Schedule\Models\Schedule::class, true)) {
                    if (! isset($data['schedulable_type']) || ! isset($data['schedulable_id'])) {
                        $user = \Illuminate\Support\Facades\Auth::user();
                        if ($user) {
                            $data['schedulable_type'] = $user::class;
                            $data['schedulable_id'] = $user->id;
                        }
                    }
                }
                return $data;
            })
            // Ensure forms are prefilled when the action is mounted programmatically
            ->mountUsing(function ($formOrSchema, array $arguments) {
                // Reset form state to avoid leftover values from previous mounts
                if ($formOrSchema instanceof \Filament\Schemas\Schema) {
                    $formOrSchema->fill([]);
                } elseif (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                    $formOrSchema->fill([]);
                }

                // Normalize nested `data` payload into top-level keys so callers that pass dates under `data` (calendar context) are handled.
                if (isset($arguments['data']) && is_array($arguments['data'])) {
                    foreach ($arguments['data'] as $k => $v) {
                        if (! array_key_exists($k, $arguments)) {
                            $arguments[$k] = $v;
                        }
                    }
                }

                // If no date arguments provided, do nothing
                if (! isset($arguments['start']) && ! isset($arguments['start_date'])) {
                    return;
                }

                $timezone = \Adultdate\Schedule\SchedulePlugin::make()->getTimezone();

                // Model-aware mapping: if the action creates Meetings or Sprints, set starts_at/ends_at datetimes,
                // otherwise provide start_date/start_time style values for Schedule forms.
                $model = $this->getModel();

                $isEventModel = false;

                // Avoid hard dependency by checking class names
                if ($model) {
                    $isEventModel = is_a($model, \Adultdate\Schedule\Models\Meeting::class, true)
                        || is_a($model, \Adultdate\Schedule\Models\Sprint::class, true)
                    ;
                }

                if ($isEventModel) {
                    // Prefer explicit date/time arguments when present
                    if (isset($arguments['start_date']) || isset($arguments['start_time'])) {
                        $startDate = $arguments['start_date'] ?? null;
                        $startTime = $arguments['start_time'] ?? '00:00';
                        $endDate = $arguments['end_date'] ?? null;
                        $endTime = $arguments['end_time'] ?? null;

                        $startsAt = null;
                        $endsAt = null;

                        if ($startDate) {
                            $startsAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $startDate . ' ' . $startTime, $timezone)->toDateTimeString();
                        }

                        if ($endDate) {
                            $et = $endTime ?? ($startTime ?? '00:00');
                            $endsAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $endDate . ' ' . $et, $timezone)->toDateTimeString();
                        } elseif (isset($arguments['end'])) {
                            $endsAt = \Carbon\Carbon::parse($arguments['end'], $timezone)->toDateTimeString();
                        }

                        $meta = $arguments['metadata'] ?? null;
                        if (is_array($meta)) {
                            $meta = count($meta) ? json_encode($meta, JSON_PRETTY_PRINT) : null;
                        }

                        $values = [
                            'starts_at' => $startsAt,
                            'ends_at' => $endsAt,
                            'metadata' => $meta,
                        ];
                    } else {
                        // Use ISO start/end values
                        $start = isset($arguments['start']) ? \Carbon\Carbon::parse($arguments['start'], $timezone) : null;
                        $end = isset($arguments['end']) ? \Carbon\Carbon::parse($arguments['end'], $timezone) : null;

                        $meta = $arguments['metadata'] ?? null;
                        if (is_array($meta)) {
                            $meta = count($meta) ? json_encode($meta, JSON_PRETTY_PRINT) : null;
                        }

                        $values = [
                            'starts_at' => $start ? $start->toDateTimeString() : null,
                            'ends_at' => $end ? $end->toDateTimeString() : null,
                            'metadata' => $meta,
                        ];
                    }
                } else {
                    // Prefer explicit date/time arguments when present
                    if (isset($arguments['start_date']) || isset($arguments['start_time'])) {
                        $meta = $arguments['metadata'] ?? null;
                        if (is_array($meta)) {
                            $meta = count($meta) ? json_encode($meta, JSON_PRETTY_PRINT) : null;
                        }

                        $values = [
                            'start_date' => $arguments['start_date'] ?? null,
                            'start_time' => $arguments['start_time'] ?? null,
                            'end_date' => $arguments['end_date'] ?? null,
                            'end_time' => $arguments['end_time'] ?? null,
                            // Ensure metadata key exists (as JSON string for CodeEditor)
                            'metadata' => $meta,
                        ];

                        $user = \Illuminate\Support\Facades\Auth::user();
                        if ($user) {
                            $values['schedulable_type'] = $user::class;
                            $values['schedulable_id'] = $user->id;
                        }
                    } else {
                        $start = \Carbon\Carbon::parse($arguments['start'], $timezone);

                        $values = [
                            'start_date' => $start->format('Y-m-d'),
                            'end_date' => isset($arguments['end']) ? \Carbon\Carbon::parse($arguments['end'], $timezone)->format('Y-m-d') : null,
                            'metadata' => $arguments['metadata'] ?? [],
                        ];

                        $user = \Illuminate\Support\Facades\Auth::user();
                        if ($user) {
                            $values['schedulable_type'] = $user::class;
                            $values['schedulable_id'] = $user->id;
                        }

                        if ($start->format('H:i:s') !== '00:00:00') {
                            $values['start_time'] = $start->format('H:i');
                        }

                        if (isset($arguments['end'])) {
                            $end = \Carbon\Carbon::parse($arguments['end'], $timezone);
                            if ($end->format('H:i:s') !== '00:00:00') {
                                $values['end_time'] = $end->format('H:i');
                            }
                        }
                    }
                }

                // Prefer Schema instances
                if ($formOrSchema instanceof \Filament\Schemas\Schema) {
                    $formOrSchema->fillPartially($values, array_keys($values));

                    return;
                }

                if (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                    $formOrSchema->fill($values);

                    return;
                }
            })
            ->after(fn (HasCalendar $livewire) => $livewire->refreshRecords())
            ->cancelParentActions()
        ;
    }
}
