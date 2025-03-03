<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSchedule extends Model
{
    use HasFactory;
    protected $table='game_schedule';
    protected $fillable = ['user_id','name','category_id','image','teams','type','created_at','updated_at','status'];

    public function gameCategory()
    {
        return $this->belongsTo(GameCategory::class,'category_id' );
    }

}
