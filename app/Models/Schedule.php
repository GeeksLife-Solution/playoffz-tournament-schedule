<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function venues()
    {
        return $this->hasMany(Venue::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function matchSchedules()
    {
        return $this->hasMany(MatchSchedule::class);
    }

    public function standings()
    {
        return $this->hasMany(Standing::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
