<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSchedule extends Model
{
    use HasFactory;
    protected $table='game_schedule';
    protected $fillable = ['user_id','name','category_id','image','teams','type','created_at','updated_at','status','u_group','court'];

    public function gameCategory()
    {
        return $this->belongsTo(GameCategory::class,'category_id' );
    }

    public function gameTeams()
    {
        return $this->hasMany(GameTeam::class, 'schedule_id', 'id');
    }

    public function gameMatch()
    {
        return $this->hasMany(GameMatch::class, 'schedule_id', 'id')->with('team1', 'team2');
    }    
    

}
