<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = ['nim', 'name', 'faculty', 'study_program'];

    public function invitation(): HasOne
    {
        return $this->hasOne(Invitation::class);
    }
}
