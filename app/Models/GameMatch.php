<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameMatch extends Model
{
    use HasFactory;
    protected $table='game_match';
    protected $fillable = ['schedule_id','team1_id','team2_id','winner_id','team1_score','team2_score','status','created_at','updated_at','round','team1_placeholder','team2_placeholder','match_number','previous_match1_id','previous_match2_id'];

    public function team1()
    {
        return $this->belongsTo(GameTeam::class, 'team1_id');
    }

    public function team2()
    {
        return $this->belongsTo(GameTeam::class, 'team2_id');
    }

    public function winner()
    {
        return $this->belongsTo(GameTeam::class, 'winner_id', 'id');
    }    
    

    // public function gameCategory()
    // {
    //     return $this->belongsTo(GameCategory::class,'category_id');
    // }
    // public function gameTournament()
    // {
    //     return $this->belongsTo(GameTournament::class,'tournament_id');
    // }
    // public function gameTeam1()
    // {
    //     return $this->belongsTo(GameTeam::class,'team1_id');
    // }
    // public function gameTeam2()
    // {
    //     return $this->belongsTo(GameTeam::class,'team2_id');
    // }
    // public function gameQuestions()
    // {
    //     return $this->hasMany(GameQuestions::class,'match_id');
    // }
    // public function activeQuestions()
    // {
    //     return $this->hasMany(GameQuestions::class,'match_id')->where('status',1);
    // }



}
