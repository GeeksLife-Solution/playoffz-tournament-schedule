<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameTeam extends Model
{
    use HasFactory;
    protected $table='game_team';
    protected $fillable = ['schedule_id','avatar','name','team_number','play_area_id','play_group_id','created_at','updated_at','status'];
}
