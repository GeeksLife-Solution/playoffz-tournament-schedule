<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSchedule extends Model
{
    use HasFactory;
    protected $table='game_schedule';
    protected $fillable = ['user_id','name','category_id','image','teams','type','created_at','updated_at','status','group_count','court_count'];

    public function gameCategory()
    {
        return $this->belongsTo(GameCategory::class,'category_id' );
    }

    
    // Relationship with GameArea (Assuming One Schedule has Many Courts/Areas)
    public function gameArea()
    {
        return $this->hasMany(GameArea::class, 'schedule_id');
    }

     // Relationship with GameGroup (Assuming One Schedule has Many Groups)
    public function gameGroup()
    {
        return $this->hasMany(GameGroup::class, 'schedule_id');
    }

    public function gameTeams()
    {
        return $this->hasMany(GameTeam::class, 'schedule_id', 'id');
    }

    public function gameMatch()
    {
        return $this->hasMany(GameMatch::class, 'schedule_id', 'id')
                    ->with(['team1', 'team2', 'playGroup', 'playArea']);
    }


     // Define relationships
    public function teams()
    {
        return $this->hasMany(GameTeam::class, 'schedule_id');
    }

    public function matches()
    {
        return $this->hasMany(GameMatch::class, 'schedule_id');
    }

    public function category()
    {
        return $this->belongsTo(GameCategory::class, 'category_id');
    }
    
    // In your GameSchedule model
    // Update the relationship to specify the foreign key
    public function gameGroups()
    {
        return $this->hasMany(GameGroup::class, 'schedule_id');
    }

    public function gameAreas()
    {
        return $this->hasMany(GameArea::class, 'schedule_id');
    }


}
