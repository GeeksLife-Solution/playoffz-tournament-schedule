<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameGroup extends Model
{
    use HasFactory;
    protected $table = "game_group";
    protected $fillable = ['schedule_id','name','created_at','updated_at'];

    public function schedule()
    {
        return $this->belongsTo(GameSchedule::class, 'schedule_id');
    }
}
