<?php

namespace App\Models;

use App\Support\SeatNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegisteredGuest extends Model
{
    protected $fillable = ['invitation_id', 'full_name', 'guest_type', 'seat_number', 'attended_at'];

    protected function casts(): array
    {
        return ['attended_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (RegisteredGuest $guest): void {
            $guest->seat_number ??= SeatNumber::forRegisteredGuest($guest->guest_type);
        });
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
