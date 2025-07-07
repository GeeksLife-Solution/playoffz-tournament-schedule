<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameMatch extends Model
{
    use HasFactory;
    protected $table='game_match';
    protected $fillable = ['schedule_id','round','team1_id','team2_id','winner_id','team1_score','team2_score','match_status','match_number','play_area_id','play_group_id','match_date','match_time' ,'team1_placeholder','team2_placeholder','previous_match1_id','previous_match2_id','is_bye'];

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

    public function previousMatch1()
    {
        return $this->belongsTo(GameMatch::class, 'team1_id', 'winner_id');
    }

    public function previousMatch2()
    {
        return $this->belongsTo(GameMatch::class, 'team2_id', 'winner_id');
    }

    public function playGroup()
    {
        return $this->belongsTo(GameGroup::class, 'play_group_id','id');
    }

    public function playArea()
    {
        return $this->belongsTo(GameArea::class, 'play_area_id','id');
    }


    public function hasWinner()
    {
        return !is_null($this->winner_id);
    }

    public function hasWinnerMoved()
    {
        if (!$this->hasWinner()) {
            return false;
        }

        return self::where('team1_id', $this->winner_id)
            ->orWhere('team2_id', $this->winner_id)
            ->exists();
    }

    public function gameSchedule()
    {
        return $this->belongsTo(GameSchedule::class, 'schedule_id'); // Adjust if the foreign key is different
    }
    
    /**
     * Determine if the match is editable.
     */
    public function isEditable()
    {
        return !($this->hasWinner() && $this->hasWinnerMoved());
    }

    /**
     * Get the team name or placeholder.
     */
    public function getTeam1Name()
    {
        return $this->team1_id == 0 ? $this->team1_placeholder : ($this->team1->name ?? 'BYE');
    }

    public function getTeam2Name()
    {
        return $this->team2_id == 0 ? $this->team2_placeholder : ($this->team2->name ?? 'BYE');
    }

    /**
     * Get Play Group Name
     */
    public function getPlayGroupName()
    {
        return $this->playGroup->name ?? 'Unassigned';
    }

    /**
     * Get Play Area Name
     */
    public function getPlayAreaName()
    {
        return $this->playArea->name ?? 'Unassigned';
    }
    
}
