<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function matchSchedules()
    {
        return $this->hasMany(MatchSchedule::class);
    }

    public function standings()
    {
        return $this->hasOne(Standing::class);
    }

}
