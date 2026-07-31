<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invitation extends Model
{
    protected $fillable = ['student_id', 'code', 'base_quota', 'extra_quota', 'package_name', 'notes'];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function registeredGuests(): HasMany { return $this->hasMany(RegisteredGuest::class); }
    public function getTotalQuotaAttribute(): int { return $this->base_quota + $this->extra_quota; }
}
