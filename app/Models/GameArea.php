<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameArea extends Model
{
    use HasFactory;
    protected $table = "game_area";
    protected $fillable = ['schedule_id','name','created_at','updated_at'];

     // Specify the correct foreign key
     public function schedule()
     {
         return $this->belongsTo(GameSchedule::class, 'schedule_id');
     }
}
