<?php

namespace Adultdate\Schedule\Filament\Widgets;

use Adultdate\Schedule\Enums\Priority;
use Adultdate\Schedule\Models\Meeting;
use Adultdate\Schedule\Models\Sprint;
use Filament\Actions\ActionGroup;
use Adultdate\Schedule\Models\CalendarSettings;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Adultdate\Schedule\Attributes\CalendarEventContent;
use Adultdate\Schedule\Filament\Actions\CreateAction;
use Adultdate\Schedule\Filament\CalendarWidget;
use Adultdate\Schedule\ValueObjects\DateClickInfo;
use Adultdate\Schedule\ValueObjects\DateSelectInfo;
use Adultdate\Schedule\ValueObjects\EventDropInfo;
use Adultdate\Schedule\ValueObjects\EventResizeInfo;
use Adultdate\Schedule\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class EventCalendar extends CalendarWidget
{
    protected string|HtmlString|bool|null $heading = 'Calendar';

    protected bool $eventClickEnabled = true;

    protected bool $eventDragEnabled = true;

    protected bool $eventResizeEnabled = true;

    protected bool $dateClickEnabled = true;

    protected bool $dateSelectEnabled = true;

       protected static ?int $sort = 1;

     public function config(): array
    {
        $settings = CalendarSettings::where('user_id', Auth::id())->first();

        $openingStart = $settings?->opening_hour_start?->format('H:i:s') ?? '09:00:00';
        $openingEnd = $settings?->opening_hour_end?->format('H:i:s') ?? '17:00:00';

        $config = [
            // Use a time-grid by default to emphasize event times; enable week/day views in the toolbar
            'initialView' => 'timeGridWeek',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            ],
            'nowIndicator' => true,
            'views' => [
                'timeGridDay' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => $openingEnd,
                    'slotHeight' => 60,
                ],
                'timeGridWeek' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => $openingEnd,
                    'slotHeight' => 60,
                ],
            ],
        ];

        return $config;
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $start = $info->start->toMutable()->startOfDay();
        $end = $info->end->toMutable()->endOfDay();

        $meetings = \Adultdate\Schedule\Models\Meeting::query()
            ->withCount('users')
            ->whereDate('ends_at', '>=', $start)
            ->whereDate('starts_at', '<=', $end)
            ->get();

        $sprints = \Adultdate\Schedule\Models\Sprint::query()
            ->whereDate('ends_at', '>=', $start)
            ->whereDate('starts_at', '<=', $end)
            ->get();

        return collect()
            ->push(...$meetings)
            ->push(...$sprints);
    }

    protected function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewAction()->label('View'),
            $this->editAction()->label('Edit'),
            $this->deleteAction()->label('Delete'),
        ];
    }

    protected function getDateClickContextMenuActions(): array
    {
        return [
            $this->createAction(\Adultdate\Schedule\Models\Meeting::class, 'ctxCreateMeeting')
                ->label('Schedule meeting')
                ->icon('heroicon-o-calendar-days')
                ->modalHeading('Schedule meeting')
                ->modalWidth('4xl')
                ->mountUsing(function (CreateAction $action, ?Schema $schema, ?DateClickInfo $info): void {
                    if (! $schema || ! $info) {
                        return;
                    }

                    $start = $info->date->toMutable()->setTime(12, 0);

                    // DateTimePicker expects a datetime string the form can parse reliably
                    $schema->fill([
                        'starts_at' => $start->toDateTimeString(),
                        'ends_at' => $start->copy()->addHour()->toDateTimeString(),
                    ]);
                }),
        ];
    }

    protected function getDateSelectContextMenuActions(): array
    {
        return [
            $this->createAction(\Adultdate\Schedule\Models\Sprint::class, 'ctxCreateSprint')
                ->label('Plan sprint')
                ->icon('heroicon-o-flag')
                ->modalHeading('Plan sprint')
                ->modalWidth('4xl')
                ->mountUsing(function (CreateAction $action, ?Schema $schema, ?DateSelectInfo $info): void {
                    if (! $schema || ! $info) {
                        return;
                    }

                    $start = $info->start->toMutable();
                    $end = $info->end->toMutable();

                    if ($info->allDay) {
                        $start->startOfDay();
                        $end->subDay()->endOfDay();
                    }

                    // DatePicker expects date strings (Y-m-d); DateTimePicker expects datetime strings
                    // Sprints use DatePicker for starts_at/ends_at, so provide date-only strings
                    $schema->fill([
                        'priority' => Priority::Medium->value,
                        'starts_at' => $start->format('Y-m-d'),
                        'ends_at' => ($end->greaterThan($start) ? $end : $start->copy()->addDay())->format('Y-m-d'),
                    ]);
                }),
        ];
    }

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        if (! $event instanceof Meeting && ! $event instanceof Sprint) {
            return false;
        }

        $event->forceFill([
            'starts_at' => $info->event->getStart(),
            'ends_at' => $info->event->getEnd(),
        ])->save();

        Notification::make()
            ->title('Event rescheduled')
            ->success()
            ->send();

        $this->refreshRecords();

        return true;
    }

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        if (! $event instanceof Sprint) {
            Notification::make()
                ->title('Only sprints can be resized')
                ->warning()
                ->send();

            return false;
        }

        $event->forceFill([
            'starts_at' => $info->event->getStart(),
            'ends_at' => $info->event->getEnd(),
        ])->save();

        Notification::make()
            ->title('Sprint duration updated')
            ->success()
            ->send();

        $this->refreshRecords();

        return true;
    }

    #[CalendarEventContent(model: \Adultdate\Schedule\Models\Meeting::class)]
    protected function meetingEventContent(): string
    {
        return view('adultdate-schedule::components.calendar.events.meeting')->render();
    }

    #[CalendarEventContent(model: \Adultdate\Schedule\Models\Sprint::class)]
    protected function sprintEventContent(): string
    {
        return view('adultdate-schedule::components.calendar.events.sprint')->render();
    }
}
