<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameMember extends Model
{
    use HasFactory;
    protected $table='game_member';
    protected $fillable = ['user_id','name','email','created_at','updated_at','status'];

}
