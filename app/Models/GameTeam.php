<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameTeam extends Model
{
    use HasFactory;
    protected $table='game_team';
    protected $fillable = ['schedule_id','name','team_number','created_at','updated_at','status'];
}
