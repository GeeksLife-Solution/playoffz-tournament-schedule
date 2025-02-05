<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scoring extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function match()
    {
        return $this->belongsTo(MatchSchedule::class);
    }

    public function winner()
    {
        return $this->belongsTo(Participant::class, 'winner_id');
    }
}
