<?php

namespace Adultdate\Schedule\Filament\Widgets;

use Adultdate\Schedule\Attributes\CalendarEventContent;
use Adultdate\Schedule\Enums\CalendarViewType;
use Adultdate\Schedule\Enums\Priority;
use Adultdate\Schedule\Filament\Actions\CreateAction;
use Adultdate\Schedule\Filament\CalendarWidget;
use Adultdate\Schedule\Models\CalendarSettings;
use Adultdate\Schedule\Models\Meeting;
use Adultdate\Schedule\Models\Sprint;
use Adultdate\Schedule\ValueObjects\DateClickInfo;
use Adultdate\Schedule\ValueObjects\DateSelectInfo;
use Adultdate\Schedule\ValueObjects\EventDropInfo;
use Adultdate\Schedule\ValueObjects\EventResizeInfo;
use Adultdate\Schedule\ValueObjects\FetchInfo;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

final class EventCalendar extends CalendarWidget
{
    protected static string $viewIdentifier = 'adultdate-schedule::calendar-widget';

    protected string | HtmlString | bool | null $heading = 'Calendar';

    protected bool $eventClickEnabled = true;

    protected bool $eventDragEnabled = true;

    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;

    protected bool $eventResizeEnabled = true;

    protected bool $dateClickEnabled = true;

    protected bool $dateSelectEnabled = true;

    protected static ?int $sort = 1;

    public function getView(): string
    {
        return 'adultdate-schedule::calendar-widget';
    }

    public function getOptions(): array
    {
        return $this->getConfig();
    }

    public function getConfig(): array
    {
        return array_replace_recursive(
            $this->config(),
            \Adultdate\Schedule\SchedulePlugin::get()->getConfig(),
        );
    }

    public function config(): array
    {
        $settings = CalendarSettings::where('user_id', Auth::id())->first();

        $openingStart = $settings?->opening_hour_start?->format('H:i:s') ?? '09:00:00';
        $openingEnd = $settings?->opening_hour_end?->format('H:i:s') ?? '17:00:00';

        $config = [
            'view' => 'dayGridMonth',
            'headerToolbar' => [
                'start' => 'title',
                'center' => '',
                'end' => 'dayGridMonth,timeGridWeek,timeGridDay, today prev,next',
            ],
            'nowIndicator' => true,
            'views' => [
                'timeGridDay' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => '24:00:00',
                    'slotHeight' => 60,
                ],
                'timeGridWeek' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => '24:00:00',
                    'slotHeight' => 60,
                ],
                'timeGridMonth' => [
                    'slotMinTime' => $openingStart,
                    'slotMaxTime' => '24:00:00',
                    'slotHeight' => 60,
                ],
            ],
        ];

        return $config;
    }

    protected function getEvents(FetchInfo $info): Collection | array | Builder
    {
        $start = $info->start->toMutable()->startOfDay();
        $end = $info->end->toMutable()->endOfDay();

        \Illuminate\Support\Facades\Log::info('getEvents called', ['start' => $start, 'end' => $end]);

        $meetings = Meeting::query()
            ->withCount('users')
            ->whereDate('ends_at', '>=', $start)
            ->whereDate('starts_at', '<=', $end)
            ->get()
        ;

        $sprints = Sprint::query()
            ->whereDate('ends_at', '>=', $start)
            ->whereDate('starts_at', '<=', $end)
            ->get()
        ;

        $events = collect()
            ->push(...$meetings)
            ->push(...$sprints)
        ;

        \Illuminate\Support\Facades\Log::info('Events returned', ['count' => $events->count()]);

        return $events;

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
            $this->createAction(Meeting::class, 'ctxCreateMeeting')
                ->label('Schedule meeting')
                ->icon('heroicon-o-calendar-days')
                ->modalHeading('Schedule meeting')
                ->modalWidth('4xl')
                ->mountUsing(function (CreateAction $action, ?Schema $schema, ?DateClickInfo $info): void {
                    if (! $schema || ! $info) {
                        return;
                    }

                    $start = $info->date->toMutable()->setTime(12, 0);

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
            $this->createAction(Sprint::class, 'ctxCreateSprint')
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

                    $schema->fill([
                        'title' => '',
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
            ->send()
        ;

        $this->refreshRecords();

        return true;
    }

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        if (! $event instanceof Sprint) {
            Notification::make()
                ->title('Only sprints can be resized')
                ->warning()
                ->send()
            ;

            return false;
        }

        $event->forceFill([
            'starts_at' => $info->event->getStart(),
            'ends_at' => $info->event->getEnd(),
        ])->save();

        Notification::make()
            ->title('Sprint duration updated')
            ->success()
            ->send()
        ;

        $this->refreshRecords();

        return true;
    }

    #[CalendarEventContent(model: Meeting::class)]
    protected function meetingEventContent(): string
    {
        return view('adultdate-schedule::components.calendar.events.meeting')->render();
    }

    #[CalendarEventContent(model: Sprint::class)]
    protected function sprintEventContent(): string
    {
        return view('adultdate-schedule::components.calendar.events.sprint')->render();
    }
}
