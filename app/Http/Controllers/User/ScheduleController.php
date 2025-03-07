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

    // public function storeSchedule(Request $request)
    // {
    //     try {
    //         // Validate the request
    //         $validatedData = $request->validate([
    //             'category' => 'required',
    //             'team' => 'required|integer|min:2',
    //             'type' => 'required|string',
    //         ]);

    //         // Retrieve category
    //         $category = GameCategory::select('name', 'id', 'image')->where('id', $validatedData['category'])->first();

    //         if (!$category) {
    //             return redirect()->back()->with('error', 'Invalid category selected.');
    //         }

    //         // Generate tournament name
    //         $scheduleName = "{$validatedData['team']} Team {$category->name} Schedule";

    //         // Start Database Transaction
    //         DB::beginTransaction();

    //         // Insert schedule data
    //         $schedule = GameSchedule::create([
    //             'category_id' => $category->id,
    //             'image' => $category->image,
    //             'name' => $scheduleName,
    //             'user_id' => Auth::id(),
    //             'teams' => $validatedData['team'],
    //             'type' => $validatedData['type'],
    //             'status' => 1
    //         ]);

    //         // Insert teams
    //         $teams = [];
    //         for ($team = 1; $team <= $validatedData['team']; $team++) {
    //             $teams[] = GameTeam::create([
    //                 'schedule_id' => $schedule->id,
    //                 'name' => "Team {$team}",
    //                 'team_number' => $team,
    //             ]);
    //         }

    //         // Generate Matches Based on Tournament Type
    //         if ($validatedData['type'] === 'league-round-robin') {
    //             $this->generateRoundRobin($schedule->id, $teams);
    //         } elseif ($validatedData['type'] === 'knockout-tournament') {
    //             $this->generateKnockout($schedule->id, $teams);
    //         } elseif ($validatedData['type'] === 'league-cum-knockout') {
    //             $this->generateLeagueCumKnockout($schedule->id, $teams);
    //         }

    //         // Commit Transaction
    //         DB::commit();

    //         return redirect()->route('user.schedule.list')->with('success', 'Tournament and Schedule created successfully!');
    //     } catch (\Exception $e) {
    //         // Rollback Transaction in case of an error
    //         DB::rollBack();
    //         return redirect()->back()->with('error', 'Something went wrong! ' . $e->getMessage());
    //     }
    // }

    // private function generateRoundRobin($scheduleId, $teams)
    // {
    //     $numTeams = count($teams);
        
    //     // Ensure an even number of teams
    //     if ($numTeams % 2 != 0) {
    //         $teams[] = null; // Placeholder for a "bye"
    //         $numTeams++;
    //     }

    //     $rounds = $numTeams - 1;
    //     $halfSize = $numTeams / 2;
    //     $matches = [];

    //     // Separate first team (fixed) and rotating teams
    //     $fixedTeam = array_shift($teams);
    //     $rotatingTeams = $teams;

    //     for ($round = 0; $round < $rounds; $round++) {
    //         for ($i = 0; $i < $halfSize; $i++) {
    //             $team1 = ($i == 0) ? $fixedTeam : $rotatingTeams[$i - 1];
    //             $team2 = $rotatingTeams[$numTeams - 2 - $i];

    //             if ($team1 && $team2) {
    //                 $matches[] = [
    //                     'team1' => $team1->id,
    //                     'team2' => $team2->id,
    //                     'round' => $round + 1,
    //                 ];
    //             }
    //         }

    //         // Rotate teams clockwise except the first fixed one
    //         array_unshift($rotatingTeams, array_pop($rotatingTeams));
    //     }

    //     // Store matches in DB
    //     $this->storeGeneratedMatches($scheduleId, $matches);
    // }

    // private function generateKnockout($scheduleId, $teams)
    // {
    //     shuffle($teams);
    //     $matches = [];
    //     $round = 1;

    //     while (count($teams) > 1) {
    //         $nextRound = [];
    //         for ($i = 0; $i < count($teams); $i += 2) {
    //             if (isset($teams[$i + 1])) {
    //                 $matches[] = [
    //                     'team1' => $teams[$i]->id,
    //                     'team2' => $teams[$i + 1]->id,
    //                     'round' => $round,
    //                 ];
    //                 $nextRound[] = $teams[$i]; // Placeholder winner (update this later)
    //             } else {
    //                 $nextRound[] = $teams[$i]; // Bye for odd team
    //             }
    //         }
    //         $teams = $nextRound;
    //         $round++;
    //     }

    //     // Store matches in DB
    //     $this->storeGeneratedMatches($scheduleId, $matches);
    // }

    // private function generateLeagueCumKnockout($scheduleId, $teams)
    // {
    //     // Step 1: Generate Round-Robin Matches
    //     $this->generateRoundRobin($scheduleId, $teams);

    //     // Step 2: Retrieve Match Results and Rank Teams
    //     $rankedTeams = $this->rankTeams($scheduleId);

    //     // Step 3: Take Top 4 (or adjust as needed)
    //     $numTopTeams = min(4, count($rankedTeams)); 
    //     $topTeams = array_slice($rankedTeams, 0, $numTopTeams);

    //     // Step 4: Generate Knockout Matches
    //     $this->generateKnockout($scheduleId, $topTeams);
    // }

    // private function storeGeneratedMatches($scheduleId, $matches)
    // {
    //     foreach ($matches as $match) {
    //         GameMatch::create([
    //             'schedule_id' => $scheduleId,
    //             'team1_id' => $match['team1'],
    //             'team2_id' => $match['team2'],
    //             'round' => $match['round'] ?? 1,
    //             'match_status' => 'pending',
    //         ]);
    //     }
    // }

    // private function rankTeams($scheduleId)
    // {
    //     $teams = GameTeam::where('schedule_id', $scheduleId)->get();

    //     $rankings = [];

    //     foreach ($teams as $team) {
    //         $matches = GameMatch::where('schedule_id', $scheduleId)
    //             ->where(function ($query) use ($team) {
    //                 $query->where('team1_id', $team->id)
    //                     ->orWhere('team2_id', $team->id);
    //             })->get();

    //         $points = 0;
    //         $goalDifference = 0;

    //         foreach ($matches as $match) {
    //             if ($match->winner_id == $team->id) {
    //                 $points += 3; // Win
    //             } elseif ($match->winner_id === null && $match->team1_score === $match->team2_score) {
    //                 $points += 1; // Draw
    //             }

    //             // Calculate goal difference
    //             if ($match->team1_id == $team->id) {
    //                 $goalDifference += ($match->team1_score - $match->team2_score);
    //             } else {
    //                 $goalDifference += ($match->team2_score - $match->team1_score);
    //             }
    //         }

    //         $rankings[] = [
    //             'team' => $team,
    //             'points' => $points,
    //             'goal_difference' => $goalDifference
    //         ];
    //     }

    //     // Sort teams: First by points, then by goal difference
    //     usort($rankings, function ($a, $b) {
    //         if ($a['points'] == $b['points']) {
    //             return $b['goal_difference'] <=> $a['goal_difference'];
    //         }
    //         return $b['points'] <=> $a['points'];
    //     });

    //     return array_column($rankings, 'team'); // Return sorted teams
    // }

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

    // public function updateMatch(Request $request)
    // {
    //     try {
    //         // Validate the incoming request
    //         $validatedData = $request->validate([
    //             'match_id' => 'required|exists:game_match,id',
    //             'team1_name' => 'required|string|max:255',
    //             'team2_name' => 'required|string|max:255',
    //             'team1_score' => 'required|integer|min:0',
    //             'team2_score' => 'required|integer|min:0',
    //         ]);

    //         // Find the match
    //         $match = GameMatch::findOrFail($validatedData['match_id']);

    //         // Update the team names in the game_teams table
    //         $team1 = GameTeam::find($match->team1_id);
    //         $team2 = GameTeam::find($match->team2_id);

    //         if ($team1) {
    //             $team1->name = $validatedData['team1_name'];
    //             $team1->save();
    //         }

    //         if ($team2) {
    //             $team2->name = $validatedData['team2_name'];
    //             $team2->save();
    //         }

    //         // Update the match scores
    //         $match->team1_score = $validatedData['team1_score'];
    //         $match->team2_score = $validatedData['team2_score'];

    //         // Determine winner
    //         if ($match->team1_score > $match->team2_score) {
    //             $match->winner_id = $match->team1_id;
    //         } elseif ($match->team1_score < $match->team2_score) {
    //             $match->winner_id = $match->team2_id;
    //         } else {
    //             $match->winner_id = null; // Draw
    //         }

    //         // Save match updates
    //         $match->save();

    //         return redirect()->back()->with('success', 'Match updated successfully!');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
    //     }
    // }


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
            $type = str_replace('-', ' ', $validatedData['type']);
            $schedule = GameSchedule::create([
                'category_id' => $category->id,
                'name' => "{$validatedData['team']} Team {$category->name} {$type} Schedule",
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
                    'name' => "Team {$team}",
                    'team_number' => $team,
                ]);
            }

            // Generate Matches Based on Tournament Type
            if ($validatedData['type'] === 'league-round-robin') {
                $this->generateRoundRobin($schedule->id, $teams);
            } elseif ($validatedData['type'] === 'knockout-tournament') {
                $this->generateKnockout($schedule->id, $teams);
            } elseif ($validatedData['type'] === 'league-cum-knockout') {
                $this->generateLeagueCumKnockout($schedule->id, $teams);
            }

            DB::commit();
            return redirect()->route('user.schedule.list')->with('success', 'Tournament and Schedule created successfully!');
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

    // private function generateKnockout($scheduleId, $teams)
    // {
    //     shuffle($teams);
    //     $matches = [];
    //     $round = 1;
    //     $matchCounter = 1;

    //     // First round matches
    //     for ($i = 0; $i < count($teams); $i += 2) {
    //         $matches[] = [
    //             'team1' => $teams[$i]->id,
    //             'team2' => $teams[$i + 1]->id ?? null, // Bye case
    //             'round' => $round,
    //             'match_number' => $matchCounter,
    //         ];
    //         $matchCounter++;
    //     }

    //     // Store first round matches
    //     $this->storeGeneratedMatches($scheduleId, $matches);

    //     // Generate placeholder matches for the next rounds
    //     $currentMatches = $matches;
    //     while (count($currentMatches) > 1) {
    //         $round++;
    //         $nextMatches = [];
    //         $matchCounter = 1;

    //         for ($i = 0; $i < count($currentMatches); $i += 2) {
    //             $nextMatches[] = [
    //                 'team1' => 0,
    //                 'team2' => $teams[$i + 1]->id ?  0 : null,
    //                 'team1_placeholder' => "Winner of Match " . $currentMatches[$i]['match_number'],
    //                 'team2_placeholder' => isset($currentMatches[$i + 1]) ? "Winner of Match " . $currentMatches[$i + 1]['match_number'] : null,
    //                 'round' => $round,
    //                 'match_number' => $matchCounter,
    //             ];
    //             $matchCounter++;
    //         }
    //         $this->storeGeneratedMatches($scheduleId, $nextMatches);
    //         $currentMatches = $nextMatches;
    //     }
    // }



    private function generateKnockout($scheduleId, $teams)
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
                    'team1_placeholder' => "Winner of Match " . $currentMatches[$i]->match_number,
                    'team2_placeholder' => isset($currentMatches[$i + 1]) ? "Winner of Match " . $currentMatches[$i + 1]->match_number : null,
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


    // Progress Knockout Rounds
    // public function progressKnockoutRound($scheduleId)
    // {
    //     $currentRound = GameMatch::where('schedule_id', $scheduleId)->max('round');
    //     $previousRoundMatches = GameMatch::where('schedule_id', $scheduleId)
    //         ->where('round', $currentRound)
    //         ->whereNotNull('winner_id')
    //         ->get();

    //     if ($previousRoundMatches->count() < 2) return;

    //     $newMatches = [];
    //     $winners = $previousRoundMatches->pluck('winner_id')->toArray();
    //     shuffle($winners);

    //     // If odd number of winners, give a bye (placeholder advances)
    //     if (count($winners) % 2 !== 0) {
    //         $winners[] = null; // Placeholder for bye team
    //     }

    //     for ($i = 0; $i < count($winners); $i += 2) {
    //         $newMatches[] = [
    //             'team1' => $winners[$i],
    //             'team2' => $winners[$i + 1] ?? null, // Handle bye
    //             'round' => $currentRound + 1,
    //         ];
    //     }
    //     $this->storeGeneratedMatches($scheduleId, $newMatches);
    // }

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
    private function generateLeagueCumKnockout($scheduleId, $teams)
    {
        $this->generateRoundRobin($scheduleId, $teams);
        $rankedTeams = $this->rankTeams($scheduleId);
        $topTeams = array_slice($rankedTeams, 0, min(4, count($rankedTeams)));
        $this->generateKnockout($scheduleId, $topTeams);
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

            return redirect()->back()->with('success', 'Match updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }


}
