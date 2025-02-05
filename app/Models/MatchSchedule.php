<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchSchedule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function participant1()
    {
        return $this->belongsTo(Participant::class, 'participant1_id');
    }

    public function participant2()
    {
        return $this->belongsTo(Participant::class, 'participant2_id');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
