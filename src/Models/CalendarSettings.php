<?php

declare(strict_types=1);

namespace Adultdate\Schedule\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CalendarSettings extends Model
{
    protected $fillable = [
        'user_id',
        'opening_hour_start',
        'opening_hour_end',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'opening_hour_start' => 'datetime',
            'opening_hour_end' => 'datetime',
        ];
    }
}
