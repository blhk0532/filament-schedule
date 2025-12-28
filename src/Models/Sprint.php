<?php

namespace Adultdate\Schedule\Models;

use Adultdate\Schedule\Enums\Priority;
use Adultdate\Schedule\Contracts\Eventable;
use Adultdate\Schedule\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sprint extends Model implements Eventable
{
    /** @use HasFactory<\Database\Factories\SprintFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'priority',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => Priority::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make($this)
            ->title($this->title)
            ->start($this->starts_at)
            ->end($this->ends_at)
            ->extendedProp('priority', $this->priority->getLabel());
    }
}
