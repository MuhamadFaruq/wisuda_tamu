<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = ['invitation_id', 'registered_guest_id', 'guest_name', 'guest_type', 'checked_in_at', 'gate'];

    protected function casts(): array { return ['checked_in_at' => 'datetime']; }
    public function invitation(): BelongsTo { return $this->belongsTo(Invitation::class); }
}
