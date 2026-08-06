<?php

namespace App\Models;

use App\Support\SeatNumber;
use Illuminate\Database\Eloquent\Model;

class InstitutionalGuest extends Model
{
    protected $fillable = [
        'code', 'full_name', 'institution', 'position', 'category',
        'phone', 'companions', 'seat_number', 'checked_in_at', 'gate', 'notes',
    ];

    protected function casts(): array
    {
        return ['checked_in_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (InstitutionalGuest $guest): void {
            $guest->seat_number ??= SeatNumber::forInstitutionalGuest();
        });
    }
}
