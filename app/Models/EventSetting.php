<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSetting extends Model
{
    protected $fillable = ['name', 'event_date', 'venue', 'period', 'is_active'];

    protected function casts(): array
    {
        return ['event_date' => 'datetime', 'is_active' => 'boolean'];
    }
}
