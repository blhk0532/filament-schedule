<?php

namespace Adultdate\Schedule\Models;

use Adultdate\Schedule\Contracts\Resourceable;
use Adultdate\Schedule\ValueObjects\CalendarResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model implements Resourceable
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function toCalendarResource(): CalendarResource
    {
        return CalendarResource::make($this)
            ->title($this->title);
    }
}
