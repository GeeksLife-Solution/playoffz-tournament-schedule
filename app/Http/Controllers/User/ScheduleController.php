<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\GameCategory;
use App\Models\GameSchedule;
use App\Models\GameTeam;
use App\Models\GameMatch;
class ScheduleController extends Controller
{
   public function listSchedule(){
    $title = "List Schedule";
    $userId = Auth::id();
    $data['schedule'] = GameSchedule::with('gameCategory')->where('user_id',$userId)->where('status',1)->orderBy('id','DESC')->get();
    return view(template() . 'user.schedule.list',compact('title','data'));
   }

   public function createSchedule(){
    $title = "Create Schedule";
    $data['category'] = GameCategory::select('name','id')->get();
    return view(template() . 'user.schedule.create', compact('title','data'));
   }

    public function Registrations(){
        $title = "Registrations";
        return view(template() . 'user.registrations.index', compact('title'));
    }

    public function editSchedule(Request $request)
    {
        $title = "Edit Schedule";
        $urlParams = decryptUrlParam($request->eq);
        $scheduleId = $urlParams['schedule_id'] ?? null;

        try {
            if (!$scheduleId) {
                return redirect()->back()->with('error', 'Invalid schedule');
            }

            $userId = Auth::id();

            // Fetch the schedule
            $data['schedule'] = GameSchedule::with('gameCategory','gameTeams','gameMatch','gameMatch.winner')
                ->where('user_id', $userId)
                ->where('id', $scheduleId)
                ->whereIn('status', [1, 0])
                ->first();

            if (!$data['schedule']) {
                return redirect()->back()->with('error', 'Schedule not found');
            }

            // dd($data);
            return view(template() . 'user.schedule.edit', compact('title', 'data'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while processing your request.');
        }
    }

    // NEW CODE
    public function storeSchedule(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'category' => 'required',
                'team' => 'required|integer|min:2',
                'type' => 'required|string',
            ]);

            // Retrieve Category
            $category = GameCategory::where('id', $validatedData['category'])->first();
            if (!$category) {
                return redirect()->back()->with('error', 'Invalid category selected.');
            }

            // Create Tournament
            DB::beginTransaction();

            $participant = $category->participant;
            $matchArea = $category->area;
            $matchType = $category->type;

            $type = str_replace('-', ' ', $validatedData['type']);
            $schedule = GameSchedule::create([
                'category_id' => $category->id,
                'name' => "{$validatedData['team']} {$participant} {$category->name} {$type} Schedule",
                'user_id' => Auth::id(),
                'image' => $category->image,
                'teams' => $validatedData['team'],
                'type' => $validatedData['type'],
                'status' => 1
            ]);

            // Generate Teams
            $teams = [];
            for ($team = 1; $team <= $validatedData['team']; $team++) {
                $teams[] = GameTeam::create([
                    'schedule_id' => $schedule->id,
                    'name' => "{$participant} {$team}",
                    'team_number' => $team,
                ]);
            }

            // Generate Matches Based on Tournament Type
            if ($validatedData['type'] === 'league-round-robin') {
                $this->generateRoundRobin($schedule->id, $teams);
            } elseif ($validatedData['type'] === 'knockout-tournament') {
                $this->generateKnockout($schedule->id, $teams,$matchType);
            } elseif ($validatedData['type'] === 'league-cum-knockout') {
                $this->generateLeagueCumKnockout($schedule->id, $teams,$matchType);
            }

            DB::commit();
            return redirect()->route('user.schedule.list')->with('success', 'Schedule created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong! ' . $e->getMessage());
        }
    }

    // Generate Round Robin Matches
    private function generateRoundRobin($scheduleId, $teams)
    {
        $numTeams = count($teams);
        if ($numTeams % 2 != 0) {
            $teams[] = null; // Bye
            $numTeams++;
        }

        $rounds = $numTeams - 1;
        $halfSize = $numTeams / 2;
        $matches = [];

        $fixedTeam = array_shift($teams);
        $rotatingTeams = $teams;

        for ($round = 0; $round < $rounds; $round++) {
            for ($i = 0; $i < $halfSize; $i++) {
                $team1 = ($i == 0) ? $fixedTeam : $rotatingTeams[$i - 1];
                $team2 = $rotatingTeams[$numTeams - 2 - $i];

                if ($team1 && $team2) {
                    $matches[] = [
                        'team1' => $team1->id,
                        'team2' => $team2->id,
                        'round' => $round + 1,
                    ];
                }
            }
            array_unshift($rotatingTeams, array_pop($rotatingTeams));
        }
        $this->storeGeneratedMatches($scheduleId, $matches);
    }


    private function generateKnockout($scheduleId, $teams, $matchType)
    {
        shuffle($teams);
        $matches = [];
        $round = 1;
        $matchCounter = 1;
        // First round matches
        for ($i = 0; $i < count($teams); $i += 2) {
            $match = GameMatch::create([
                'schedule_id' => $scheduleId,
                'team1_id' => $teams[$i]->id,
                'team2_id' => $teams[$i + 1]->id ?? null, // Bye case
                'round' => $round,
                'match_number' => $matchCounter,
                'match_status' => 'pending',
            ]);

            $matches[] = $match;
            $matchCounter++;
        }

        // Generate placeholder matches for the next rounds
        $currentMatches = $matches;
        while (count($currentMatches) > 1) {
            $round++;
            $nextMatches = [];
            $matchCounter = 1;

            for ($i = 0; $i < count($currentMatches); $i += 2) {
                $match = GameMatch::create([
                    'schedule_id' => $scheduleId,
                    'team1_id' => null,
                    'team2_id' => null,
                    'team1_placeholder' => "Winner of ".$matchType." ". $currentMatches[$i]->match_number,
                    'team2_placeholder' => isset($currentMatches[$i + 1]) ? "Winner of ".$matchType." ". $currentMatches[$i + 1]->match_number : null,
                    'round' => $round,
                    'match_number' => $matchCounter,
                    'previous_match1_id' => $currentMatches[$i]->id,
                    'previous_match2_id' => isset($currentMatches[$i + 1]) ? $currentMatches[$i + 1]->id : null,
                ]);

                $nextMatches[] = $match;
                $matchCounter++;
            }

            $currentMatches = $nextMatches;
        }
    }

    private function progressKnockoutRound($scheduleId)
    {
        $matchesToUpdate = GameMatch::where('schedule_id', $scheduleId)
            ->whereNull('team1_id')
            ->orWhereNull('team2_id')
            ->get();

        foreach ($matchesToUpdate as $match) {
            if ($match->previous_match1_id) {
                $previousMatch1 = GameMatch::find($match->previous_match1_id);
                if ($previousMatch1 && $previousMatch1->winner_id) {
                    $match->team1_id = $previousMatch1->winner_id;
                    $match->team1_placeholder = null;
                }
            }

            if ($match->previous_match2_id) {
                $previousMatch2 = GameMatch::find($match->previous_match2_id);
                if ($previousMatch2 && $previousMatch2->winner_id) {
                    $match->team2_id = $previousMatch2->winner_id;
                    $match->team2_placeholder = null;
                }
            }

            $match->save();
        }
    }

    // League Cum Knockout (First Round Robin, Then Knockout)
    private function generateLeagueCumKnockout($scheduleId, $teams, $matchType)
    {
        $this->generateRoundRobin($scheduleId, $teams);
        $rankedTeams = $this->rankTeams($scheduleId);
        $topTeams = array_slice($rankedTeams, 0, min(4, count($rankedTeams)));
        $this->generateKnockout($scheduleId, $topTeams,$matchType);
    }

    // Store Matches in Database
    private function storeGeneratedMatches($scheduleId, $matches)
    {
        foreach ($matches as $match) {
            GameMatch::create([
                'schedule_id' => $scheduleId,
                'team1_id' => $match['team1'],
                'team2_id' => $match['team2'],
                'round' => $match['round'],
                'match_status' => 'pending',
                'team1_placeholder'=> $match['team1_placeholder'] ?? null,
                'team2_placeholder'=> $match['team2_placeholder'] ?? null,
            ]);
        }
    }

    public function updateMatch(Request $request)
    {
        try {
            // Validate the incoming request
            $validatedData = $request->validate([
                'match_id' => 'required|exists:game_match,id',
                'team1_name' => 'required|string|max:255',
                'team2_name' => 'required|string|max:255',
                'team1_score' => 'required|integer|min:0',
                'team2_score' => 'required|integer|min:0',
            ]);

            // Find the match
            $match = GameMatch::findOrFail($validatedData['match_id']);

            // Update team names
            $team1 = GameTeam::find($match->team1_id);
            $team2 = GameTeam::find($match->team2_id);

            if ($team1) {
                $team1->name = $validatedData['team1_name'];
                $team1->save();
            }

            if ($team2) {
                $team2->name = $validatedData['team2_name'];
                $team2->save();
            }

            // Update match scores
            $match->team1_score = $validatedData['team1_score'];
            $match->team2_score = $validatedData['team2_score'];

            // Determine winner
            if ($match->team1_score > $match->team2_score) {
                $match->winner_id = $match->team1_id;
            } elseif ($match->team1_score < $match->team2_score) {
                $match->winner_id = $match->team2_id;
            } else {
                $match->winner_id = null;
            }

            $match->save();

            // Progress the knockout round by updating placeholders
            $this->progressKnockoutRound($match->schedule_id);

            return redirect()->back()->with('success', 'Updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }


}
