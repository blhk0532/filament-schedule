<?php

namespace Adultdate\Schedule\Models;

use App\Models\User;
use Adultdate\Schedule\Contracts\Eventable;
use Adultdate\Schedule\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
 
class Meeting extends Model implements Eventable
{
    /** @use HasFactory<\Database\Factories\MeetingFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function toCalendarEvent(): CalendarEvent
    {
        $participants = $this->users_count ?? $this->users()->count();

        return CalendarEvent::make($this)
            ->title($this->title)
            ->start($this->starts_at)
            ->end($this->ends_at)
            ->durationEditable(false)
            ->extendedProp('participants', $participants);
    }
}
