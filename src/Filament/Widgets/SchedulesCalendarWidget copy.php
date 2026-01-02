<?php

namespace Adultdate\Schedule\Filament\Widgets;

use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language as CodeLanguage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Adultdate\Schedule\Filament\Widgets\FullCalendarWidget;
use Adultdate\Schedule\Enums\Frequency;
use Adultdate\Schedule\Enums\ScheduleTypes;
use Adultdate\Schedule\Models\Schedule;
use Adultdate\Schedule\Concerns\HasHeaderActions;
use Adultdate\Schedule\Filament\Widgets\Concerns\CanBeConfigured;
use Adultdate\Schedule\Filament\Widgets\Concerns\InteractsWithRawJS;
use Adultdate\Schedule\Filament\Widgets\Concerns\InteractsWithEvents;
use Adultdate\Schedule\Concerns\HasSchema;
use Adultdate\Schedule\Concerns\CanRefreshCalendar;
use Adultdate\Schedule\Concerns\InteractsWithEventRecord;
use Adultdate\Schedule\ValueObjects\DateClickInfo;
use Adultdate\Schedule\ValueObjects\NoEventsClickInfo;

use Adultdate\Schedule\Contracts\HasCalendar;

class SchedulesCalendarWidget extends FullCalendarWidget implements HasCalendar
{
    use HasHeaderActions, CanBeConfigured, InteractsWithRawJS, InteractsWithEvents, HasSchema, CanRefreshCalendar, InteractsWithEventRecord, \Adultdate\Schedule\Concerns\InteractsWithCalendar, \Adultdate\Schedule\Concerns\HasOptions {
        // Prefer the contract-compatible refreshRecords (chainable) from CanRefreshCalendar
        CanRefreshCalendar::refreshRecords insteadof InteractsWithEvents;

        // Keep the frontend-only refresh available under an alias if needed
        InteractsWithEvents::refreshRecords as refreshRecordsFrontend;

        // When both traits define the same handler, prefer the widget-centric implementation
        InteractsWithEvents::onEventClick insteadof \Adultdate\Schedule\Concerns\InteractsWithCalendar;
        InteractsWithEvents::onDateSelect insteadof \Adultdate\Schedule\Concerns\InteractsWithCalendar;

        // Resolve getOptions collision: prefer HasOptions' getOptions which merges config and options
        \Adultdate\Schedule\Concerns\HasOptions::getOptions insteadof CanBeConfigured;
    }
    /**
     * Return FullCalendar config overrides for this widget.
     */
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 3;

    // Enable clicking/selecting/no-events click by overriding the trait-enabled checks
    public function isDateClickEnabled(): bool
    {
        return true;
    }

    public function isDateSelectEnabled(): bool
    {
        return true;
    }

    public function isNoEventsClickEnabled(): bool
    {
        return true;
    }

    public function config(): array
    {
        return [
            'initialView' => 'timeGridDay',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            ],
            // Make calendar interactive
            'editable' => true,
            'selectable' => true,
            'selectMirror' => true,

            // Show the current time indicator (red line)
            'nowIndicator' => true,
            'nowIndicatorSnap' => 'auto',

            // sensible slot bounds for timeGrid views
            'slotMinTime' => '06:00:00',
            'slotMaxTime' => '22:00:00',
        ];
    }

    /**
     * Customize header actions (prefill create form when user selects a date range).
     */
    public function getHeaderActions(): array
    {
        return [
            \Adultdate\Schedule\Actions\CreateAction::make()
                ->mountUsing(function ($formOrSchema, array $arguments) {
                    // Reset form state to avoid leftover values from previous mounts
                    if ($formOrSchema instanceof \Filament\Schemas\Schema) {
                        $formOrSchema->fill([]);
                    } elseif (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                        $formOrSchema->fill([]);
                    }

                    // Debug: record the type of the mounted object and whether we can inspect state
                    $type = is_object($formOrSchema) ? get_class($formOrSchema) : gettype($formOrSchema);
                    $canGetState = method_exists($formOrSchema, 'getState') || method_exists($formOrSchema, 'get') || method_exists($formOrSchema, 'getStatePath');

                    // If no date was provided, mount empty defaults and return
                    if (! isset($arguments['start']) && ! isset($arguments['start_date'])) {
                        return;
                    }

                    $timezone = \Adultdate\Schedule\SchedulePlugin::make()->getTimezone();

                    // Prefer explicit date/time arguments when present
                    if (isset($arguments['start_date']) || isset($arguments['start_time'])) {
                        $values = [
                            'start_date' => $arguments['start_date'] ?? null,
                            'start_time' => $arguments['start_time'] ?? null,
                            'end_date' => $arguments['end_date'] ?? null,
                            'end_time' => $arguments['end_time'] ?? null,
                            // Ensure metadata key exists so the action schema can entangle safely
                            'metadata' => $arguments['metadata'] ?? [],
                        ];
                    } else {
                        $start = \Carbon\Carbon::parse($arguments['start'], $timezone);

                        $values = [
                            'start_date' => $start->format('Y-m-d'),
                            'end_date' => isset($arguments['end']) ? \Carbon\Carbon::parse($arguments['end'], $timezone)->format('Y-m-d') : null,
                            'metadata' => $arguments['metadata'] ?? [],
                        ];

                        // If the selection included a time, prefill start_time/end_time
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

                    // Support Schema instances and other mount types depending on how Filament mounts the action
                    if ($formOrSchema instanceof \Filament\Schemas\Schema) {
                        // fillPartially will only hydrate the provided state paths
                        $formOrSchema->fillPartially($values, array_keys($values));

                        return;
                    }

                    // If the mounted object supports fill(), call it
                    if (is_object($formOrSchema) && method_exists($formOrSchema, 'fill')) {
                        $formOrSchema->fill($values);

                        return;
                    }
                })
                // Ensure required fields for Schedule creation exist and default times are set
                ->mutateDataUsing(function (array $data): array {
                    // Ensure schedulable points to the current user by default
                    if (! isset($data['schedulable_type'])) {
                        $data['schedulable_type'] = \App\Models\User::class;
                    }

                    if (! isset($data['schedulable_id'])) {
                        $data['schedulable_id'] = Auth::id();
                    }

                    // Default times if not provided
                    if (! isset($data['start_time']) || blank($data['start_time'])) {
                        $data['start_time'] = '09:00';
                    }

                    if (! isset($data['end_time']) || blank($data['end_time'])) {
                        // default to one hour after start_time
                        $start = \Carbon\Carbon::createFromFormat('H:i', $data['start_time']);
                        $data['end_time'] = $start->copy()->addHour()->format('H:i');
                    }

                    // Ensure metadata exists so mounted action schemas can entangle reliably
                    if (! isset($data['metadata'])) {
                        $data['metadata'] = [];
                    }

                    // Add initial_period payload so the after hook has a reliable source of truth
                    $data['initial_period'] = [
                        'date' => $data['start_date'] ?? now()->format('Y-m-d'),
                        'start_time' => $data['start_time'],
                        'end_time' => $data['end_time'],
                    ];

                    return $data;
                })
                ->using(function (array $data, string $model) {
                    // Use the model to create the primary Schedule
                    $record = $model::create(Arr::except($data, ['initial_period', 'start_time', 'end_time']));

                    // Determine period data from initial_period or form fields
                    if (isset($data['initial_period'])) {
                        $p = $data['initial_period'];
                        $date = $p['date'];
                        $startTime = $p['start_time'];
                        $endTime = $p['end_time'];
                    } else {
                        $date = $data['start_date'] ?? now()->format('Y-m-d');
                        $startTime = $data['start_time'] ?? '09:00';
                        $endTime = $data['end_time'] ?? \Carbon\Carbon::createFromFormat('H:i', $startTime)->addHour()->format('H:i');
                    }

                    // Use explicit creation to avoid relationship edge cases in tests
                    try {
                        $created = \Adultdate\Schedule\Models\SchedulePeriod::create([
                            'schedule_id' => $record->id,
                            'date' => $date,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'is_available' => true,
                        ]);

                    } catch (\Throwable $e) {
                        // Log to the app logger so it appears in usual channels during tests and runtime
                        logger()->error('SchedulePeriod create failed', ['error' => $e->getMessage()]);
                    }

                    return $record;
                })
                ->after(function (\Filament\Actions\CreateAction $action, Schedule $record) {
                    // Prefer mutated data if available, fallback to raw data
                    $raw = $action->getData() ?: $action->getRawData();

                    // Debug: log the action data to inspect why periods may not be created


                    // Refresh calendar and notify user
                    $this->refreshRecords();

                    Notification::make()
                        ->title('Schedule created')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * Provide a form schema for the CreateAction modal shown in the widget header.
     * Without this, the Create modal will be empty (default is an empty array).
     */
    public function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),

            Select::make('schedule_type')
                ->label('Type')
                ->options(collect(ScheduleTypes::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])->toArray())
                ->required(),

            DatePicker::make('start_date')
                ->label('Start date')
                ->required(),

            TimePicker::make('start_time')
                ->label('Start time')
                ->visible(fn ($get) => filled($get('start_date'))),

            DatePicker::make('end_date')
                ->label('End date'),

            TimePicker::make('end_time')
                ->label('End time')
                ->visible(fn ($get) => filled($get('end_date'))),

            Select::make('frequency')
                ->label('Recurrence')
                ->options(collect(Frequency::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])->toArray())
                ->nullable(),

            CheckboxList::make('frequency_config.days')
                ->label('Week days')
                ->options([
                    'monday' => 'Monday',
                    'tuesday' => 'Tuesday',
                    'wednesday' => 'Wednesday',
                    'thursday' => 'Thursday',
                    'friday' => 'Friday',
                    'saturday' => 'Saturday',
                    'sunday' => 'Sunday',
                ])
                ->visible(fn ($get) => in_array($get('frequency'), array_map(fn ($c) => $c->value, Frequency::filteredByWeekday()))),

            // Metadata as pretty JSON; dehydrate to array for model
            CodeEditor::make('metadata')
                ->label('Metadata')
                ->language(CodeLanguage::Json)
                ->helperText('Valid JSON object; will be stored as array')
                ->rules(['nullable', 'json'])
                ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : null)
                ->dehydrateStateUsing(fn ($state) => $state ? json_decode($state, true) : null),
        ];
    }

    /**
     * Add a tooltip and small tweaks when an event is rendered.
     */
    public function eventDidMount(): string
    {
        return <<<'JS'
            function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
                // Set native title attribute for a browser tooltip
                el.setAttribute('title', event.title + (timeText ? '\n' + timeText : ''));

                // Also set x-tooltip so Filament's tooltip works if available
                el.setAttribute("x-tooltip", "tooltip");
                el.setAttribute("x-data", "{ tooltip: '" + event.title.replace("'", "\'") + "' }");
            }
        JS;
    }

    /**
     * Persist period changes when an event is dropped (drag & drop).
     */
    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        // We use extendedProps.period_id if present (set in fetchEvents)
        $periodId = $event['extendedProps']['period_id'] ?? null;

        if ($periodId) {
            $period = \Adultdate\Schedule\Models\SchedulePeriod::find($periodId);

            if ($period) {
                $timezone = \Adultdate\Schedule\SchedulePlugin::make()->getTimezone();

                $start = \Carbon\Carbon::parse($event['start'], $timezone);
                $period->date = $start->format('Y-m-d');
                $period->start_time = $start->format('H:i');

                if (isset($event['end'])) {
                    $end = \Carbon\Carbon::parse($event['end'], $timezone);
                    $period->end_time = $end->format('H:i');
                }

                $period->save();

                // Optionally dispatch an event to refresh calendar
                $this->refreshRecords();
            }
        }

        // return whether to revert - false means do not revert
        return false;
    }

    /**
     * Persist period changes when an event is resized (duration changed).
     */
    public function onEventResize(array $event, array $oldEvent, array $relatedEvents, array $startDelta, array $endDelta): bool
    {
        $periodId = $event['extendedProps']['period_id'] ?? null;

        if ($periodId) {
            $period = \Adultdate\Schedule\Models\SchedulePeriod::find($periodId);

            if ($period) {
                $timezone = \Adultdate\Schedule\SchedulePlugin::make()->getTimezone();

                if (isset($event['start'])) {
                    $start = \Carbon\Carbon::parse($event['start'], $timezone);
                    $period->date = $start->format('Y-m-d');
                    $period->start_time = $start->format('H:i');
                }

                if (isset($event['end'])) {
                    $end = \Carbon\Carbon::parse($event['end'], $timezone);
                    $period->end_time = $end->format('H:i');
                }

                $period->save();

                $this->refreshRecords();
            }
        }

        return false;
    }

    /**
     * Ensure selected date ranges mount the widget's create action with normalized ISO strings
     * so the Create modal can prefill start/end date and time reliably.
     */
    public function onDateSelect(mixed $startOrInfo, mixed $end = null, mixed $allDay = false, mixed $view = null, mixed $resource = null): void
    {
        // Support both the older signature (start, end, allDay, view, resource) and the
        // new DateSelectInfo object (when called via onDateSelectJs -> the trait will pass a DateSelectInfo).
        if ($startOrInfo instanceof \Adultdate\Schedule\ValueObjects\DateSelectInfo) {
            $info = $startOrInfo;

            $startIso = $info->start->toIsoString();
            $endIso = $info->end?->toIsoString();

            $startDate = $info->start->format('Y-m-d');
            $startTime = $info->start->format('H:i');
            $endDate = $info->end?->format('Y-m-d');
            $endTime = $info->end?->format('H:i');

            $allDay = $info->allDay;
            $resource = null;
        } else {
            // Old signature
            [$startCarbon, $endCarbon] = $this->calculateTimezoneOffset($startOrInfo, $end, $allDay);

            $startIso = $startCarbon->toIsoString();
            $endIso = $endCarbon ? $endCarbon->toIsoString() : null;

            $startDate = $startCarbon->format('Y-m-d');
            $startTime = $startCarbon->format('H:i');
            $endDate = $endCarbon ? $endCarbon->format('Y-m-d') : null;
            $endTime = $endCarbon ? $endCarbon->format('H:i') : null;
        }

        $payload = [
            'type' => 'select',
            'start' => $startIso,
            'end' => $endIso,
            'start_date' => $startDate,
            'start_time' => $startTime,
            'end_date' => $endDate,
            'end_time' => $endTime,
            'allDay' => $allDay,
            'resource' => $resource,
            // Add an explicit `data` payload so mountedActions.0.data.* entanglement works regardless of form field naming
            'data' => [
                // schedule-style
                'start_date' => $startDate,
                'start_time' => $startTime,
                'end_date' => $endDate,
                'end_time' => $endTime,
                // event-style (combined ISO datetimes)
                'starts_at' => $startIso,
                'ends_at' => $endIso,
                // Provide current user id so entangled fields exist
                'user_id' => Auth::user()?->id,
            ],
        ];

        // Debug: record mount payload so we can inspect what the frontend supplied


        $this->mountAction('create', $payload);

        // Debug: inspect mountedActions last entry
        try {
            $last = end($this->mountedActions);

            $args = null;

            if (is_object($last) && method_exists($last, 'getArguments')) {
                try {
                    $args = $last->getArguments();
                } catch (\Throwable $_) {
                    $args = null;
                }
            } elseif (is_array($last) && isset($last['arguments'])) {
                $args = $last['arguments'];
            }


        } catch (\Throwable $e) {
            file_put_contents(storage_path('logs/schedule_mount_debug.log'), now()->toDateTimeString() . ' | mountedActions inspect failed: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }

        // Ensure the modal syncs to open on the frontend
        $newIndex = max(0, count($this->mountedActions) - 1);
        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
    }

    /**
     * Handle a single date click (not a range selection) to open the Create modal prefilled.
     */
    public function onDateClick(DateClickInfo $info): void
    {
        $startIso = $info->date->toIsoString();
        $allDay = $info->allDay;

        [$startCarbon, $endCarbon] = $this->calculateTimezoneOffset($startIso, null, $allDay);

        $startIso = $startCarbon->toIsoString();
        $startDate = $startCarbon->format('Y-m-d');
        $startTime = $startCarbon->format('H:i');

        $payload = [
            'type' => 'click',
            'start' => $startIso,
            'start_date' => $startDate,
            'start_time' => $startTime,
            'allDay' => $allDay,
            'resource' => null,
            // Add data payload for entanglement (both formats)
            'data' => [
                'start_date' => $startDate,
                'start_time' => $startTime,
                'starts_at' => $startIso,
            ],
        ];



        $this->mountAction('create', $payload);

        // Debug: inspect mountedActions last entry
        try {
            $last = end($this->mountedActions);
            // debugging removed
        } catch (\Throwable $e) {
            // ignored
        }

        // Ensure the modal syncs to open on the frontend
        $newIndex = max(0, count($this->mountedActions) - 1);
        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
    }

    /**
     * Date selection handled by `onDateSelect(mixed ...)` above (supports both legacy signature and DateSelectInfo).
     */

    /**
     * If the calendar supports clicking empty space, allow creating a schedule without a preset date/time.
     */
    public function onNoEventsClick(NoEventsClickInfo $info): void
    {
        $payload = [
            'type' => 'click',
        ];



        $this->mountAction('create', $payload);

        // Debug: inspect mountedActions last entry
        try {
            $last = end($this->mountedActions);
            // debugging removed
        } catch (\Throwable $e) {
            // ignored
        }

        $newIndex = max(0, count($this->mountedActions) - 1);
        $this->dispatch('sync-action-modals', id: $this->getId(), newActionNestingIndex: $newIndex);
    }

    public function getModel(): string
    {
        return Schedule::class;
    }

    public function fetchEvents(array $info): array
    {
        $start = Carbon::parse($info['start']);
        $end = Carbon::parse($info['end']);

        $user = filament()->auth()->user();

        $events = [];

        // Iterate day by day and collect schedule periods
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $schedules = Schedule::forDate($date->format('Y-m-d'))
                ->where('is_active', true)
                ->where('schedulable_type', $user::class)
                ->where('schedulable_id', $user->id)
                ->get();

            foreach ($schedules as $schedule) {
                foreach ($schedule->periods()->forDate($date->format('Y-m-d'))->available()->get() as $period) {
                    $events[] = [
                        'id' => $schedule->id.'-'.$period->id,
                        'title' => $schedule->name,
                        'start' => $period->start_date_time->toIsoString(),
                        'end' => $period->end_date_time->toIsoString(),
                        'allDay' => false,
                        'extendedProps' => [
                            'schedule_id' => $schedule->id,
                            'period_id' => $period->id,
                        ],
                    ];
                }
            }
        }

        return $events;
    }

    /**
     * Adapter for Guava calendar's `getEvents` contract which expects a FetchInfo VO.
     */
    protected function getEvents(\Adultdate\Schedule\ValueObjects\FetchInfo $info): \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Builder|array
    {
        return $this->fetchEvents([
            'start' => $info->start->toIsoString(),
            'end' => $info->end->toIsoString(),
        ]);
    }

    public function eventAssetUrl(): string
    {
        return \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('calendar-event', 'adultdate/schedule');
    }
}
