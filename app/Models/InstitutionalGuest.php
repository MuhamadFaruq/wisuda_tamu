<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitutionalGuest extends Model
{
    protected $fillable = [
        'code', 'full_name', 'institution', 'position', 'category',
        'phone', 'companions', 'checked_in_at', 'gate', 'notes',
    ];

    protected function casts(): array
    {
        return ['checked_in_at' => 'datetime'];
    }
}
