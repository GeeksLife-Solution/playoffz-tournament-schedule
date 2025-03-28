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
use App\Models\GameArea;
use App\Models\GameGroup;
use Illuminate\Support\Facades\Storage;

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

    // public function editSchedule(Request $request)
    // {
    //     $title = "Edit Schedule";
    //     $urlParams = decryptUrlParam($request->eq);
    //     $scheduleId = $urlParams['schedule_id'] ?? null;

    //     try {
    //         if (!$scheduleId) {
    //             return redirect()->back()->with('error', 'Invalid schedule');
    //         }

    //         $userId = Auth::id();

    //         // Fetch the schedule
    //         $data['schedule'] = GameSchedule::with('gameCategory','gameTeams','gameMatch','gameMatch.winner')
    //             ->where('user_id', $userId)
    //             ->where('id', $scheduleId)
    //             ->whereIn('status', [1, 0])
    //             ->first();
            
    //             dd($data);

    //         if (!$data['schedule']) {
    //             return redirect()->back()->with('error', 'Schedule not found');
    //         }
    //         return view(template() . 'user.schedule.edit', compact('title', 'data'));

    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'An error occurred while processing your request.');
    //     }
    // }

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

            // Fetch the schedule with all related data
            $data['schedule'] = GameSchedule::with([
                'gameCategory',
                'gameTeams',
                'gameMatch',
                'gameMatch.winner',
                'gameGroups', // Add this relationship
                'gameAreas'   // Add this relationship
            ])
            ->where('user_id', $userId)
            ->where('id', $scheduleId)
            ->whereIn('status', [1, 0])
            ->first();

            if (!$data['schedule']) {
                return redirect()->back()->with('error', 'Schedule not found');
            }
            
            return view(template() . 'user.schedule.edit', compact('title', 'data'));

        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while processing your request.');
        }
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

    // public function updateMatch(Request $request)
    // {
    //     try {
    //         // Validate the incoming request
    //         $validatedData = $request->validate([
    //             'match_id' => 'required|exists:game_match,id',
    //             'team1_name' => 'nullable|string|max:255',
    //             'team2_name' => 'nullable|string|max:255',
    //             'team1_score' => 'nullable|integer|min:0',
    //             'team2_score' => 'nullable|integer|min:0',
    //             'play_group_id' => 'required|exists:game_group,id',
    //             'play_group_name' => 'required|string|max:255',
    //             'play_area_id' => 'required|exists:game_area,id',
    //             'play_area_name' => 'required|string|max:255',
    //             'match_date' => 'nullable|date',
    //             'match_time' => 'nullable',
    //         ]);

    //         // Find the match
    //         $match = GameMatch::findOrFail($validatedData['match_id']);

    //         // Update team names if they exist
    //         if ($match->team1_id) {
    //             $team1 = GameTeam::find($match->team1_id);
    //             if ($team1 && !empty($validatedData['team1_name'])) {
    //                 $team1->name = $validatedData['team1_name'];
    //                 $team1->save();
    //             }
    //         }

    //         if ($match->team2_id) {
    //             $team2 = GameTeam::find($match->team2_id);
    //             if ($team2 && !empty($validatedData['team2_name'])) {
    //                 $team2->name = $validatedData['team2_name'];
    //                 $team2->save();
    //             }
    //         }

    //         // Update group name
    //         $group = GameGroup::find($validatedData['play_group_id']);
    //         if ($group) {
    //             $group->name = $validatedData['play_group_name'];
    //             $group->save();
    //         }

    //         // Update area name
    //         $area = GameArea::find($validatedData['play_area_id']);
    //         if ($area) {
    //             $area->name = $validatedData['play_area_name'];
    //             $area->save();
    //         }

    //         // Update match details
    //         $match->play_group_id = $validatedData['play_group_id'];
    //         $match->play_area_id = $validatedData['play_area_id'];
    //         $match->match_date = $validatedData['match_date'];
    //         $match->match_time = $validatedData['match_time'];

    //         // Check if match involves a BYE
    //         if (!$match->team2_id || $match->team1_placeholder == 'BYE' || $match->team2_placeholder == 'BYE') {
    //             $match->is_bye = 1;
    //             $match->winner_id = $match->team1_id ?? $match->team2_id;
    //         } else {
    //             // Update scores
    //             $match->team1_score = $validatedData['team1_score'];
    //             $match->team2_score = $validatedData['team2_score'];

    //             // Determine winner based on scores
    //             if ($match->team1_score > $match->team2_score) {
    //                 $match->winner_id = $match->team1_id;
    //             } elseif ($match->team1_score < $match->team2_score) {
    //                 $match->winner_id = $match->team2_id;
    //             } else {
    //                 $match->winner_id = null; // No winner if scores are tied
    //             }
    //         }

    //         $match->save();

    //         // Progress knockout round
    //         $this->progressKnockoutRound($match->schedule_id);

    //         return redirect()->back()->with('success', 'Match updated successfully!');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
    //     }
    // }

    public function updateMatch(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'match_id' => 'required|exists:game_match,id',
                'team1_name' => 'nullable|string|max:255',
                'team2_name' => 'nullable|string|max:255',
                'team1_score' => 'nullable|integer|min:0',
                'team2_score' => 'nullable|integer|min:0',
                'play_group_id' => 'required|exists:game_group,id',
                'play_group_name' => 'required|string|max:255',
                'play_area_id' => 'required|exists:game_area,id',
                'play_area_name' => 'required|string|max:255',
                'team1_avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'team2_avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'match_date' => 'nullable|date',
                'match_time' => 'nullable',
            ]);

            // Find the match
            $match = GameMatch::findOrFail($validatedData['match_id']);

            // Update team names and avatars if they exist
            if ($match->team1_id) {
                $team1 = GameTeam::find($match->team1_id);
                if ($team1) {
                    $team1->name = $validatedData['team1_name'];
                    
                    // Handle Team 1 Avatar
                    if ($request->hasFile('team1_avatar')) {
                        $file = $request->file('team1_avatar');
                        $fileName = time() . '_' . $file->getClientOriginalName(); // Unique filename
                        $filePath = 'team_avatars/' . $fileName;
                        
                        // Store file using the public disk
                        Storage::disk('public')->putFileAs('team_avatars', $file, $fileName);
                        
                        // Delete old avatar if exists
                        if ($team1->avatar && Storage::disk('public')->exists($team1->avatar)) {
                            Storage::disk('public')->delete($team1->avatar);
                        }
                        
                        // Save the relative path in the database
                        $team1->avatar = "/storage/app/public/".$filePath;
                    }
                    
                    $team1->save();
                }
            }

            if ($match->team2_id) {
                $team2 = GameTeam::find($match->team2_id);
                if ($team2) {
                    $team2->name = $validatedData['team2_name'];
                    
                    // Handle Team 2 Avatar
                    if ($request->hasFile('team2_avatar')) {
                        $file = $request->file('team2_avatar');
                        $fileName = time() . '_' . $file->getClientOriginalName(); // Unique filename
                        $filePath = 'team_avatars/' . $fileName;
                        
                        // Store file using the public disk
                        Storage::disk('public')->putFileAs('team_avatars', $file, $fileName);
                        
                        // Delete old avatar if exists
                        if ($team2->avatar && Storage::disk('public')->exists($team2->avatar)) {
                            Storage::disk('public')->delete($team2->avatar);
                        }
                        
                        // Save the relative path in the database
                        $team2->avatar = "/storage/app/public/".$filePath;
                    }
                    
                    $team2->save();
                }
            }

            // Update group name
            $group = GameGroup::find($validatedData['play_group_id']);
            if ($group) {
                $group->name = $validatedData['play_group_name'];
                $group->save();
            }

            // Update area name
            $area = GameArea::find($validatedData['play_area_id']);
            if ($area) {
                $area->name = $validatedData['play_area_name'];
                $area->save();
            }

            // Update match details
            $match->play_group_id = $validatedData['play_group_id'];
            $match->play_area_id = $validatedData['play_area_id'];
            $match->match_date = $validatedData['match_date'];
            $match->match_time = $validatedData['match_time'];

            // Check if match involves a BYE
            if (!$match->team2_id || $match->team1_placeholder == 'BYE' || $match->team2_placeholder == 'BYE') {
                $match->is_bye = 1;
                $match->winner_id = $match->team1_id ?? $match->team2_id;
            } else {
                // Update scores
                $match->team1_score = $validatedData['team1_score'];
                $match->team2_score = $validatedData['team2_score'];

                // Determine winner based on scores
                if ($match->team1_score > $match->team2_score) {
                    $match->winner_id = $match->team1_id;
                } elseif ($match->team1_score < $match->team2_score) {
                    $match->winner_id = $match->team2_id;
                } else {
                    $match->winner_id = null; // No winner if scores are tied
                }
            }

            $match->save();

            // Progress knockout round
            $this->progressKnockoutRound($match->schedule_id);

            return redirect()->back()->with('success', 'Match updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
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
                'court' => 'required|integer|min:1',
                'group' => 'required|integer|min:1',
            ]);
    
            // Retrieve Category
            $category = GameCategory::find($validatedData['category']);
            if (!$category) {
                return redirect()->back()->with('error', 'Invalid category selected.');
            }
    
            DB::beginTransaction();
    
            $participant = $category->participant;
            $matchArea = $category->area;
            $type = str_replace('-', ' ', $validatedData['type']);
    
            $schedule = GameSchedule::create([
                'category_id' => $category->id,
                'name' => "{$validatedData['team']} {$participant} {$category->name} {$type} Schedule",
                'user_id' => Auth::id(),
                'image' => $category->image,
                'teams' => $validatedData['team'],
                'type' => $validatedData['type'],
                'status' => 1,
                'court_count' => $validatedData['court'],
                'group_count' => $validatedData['group'],
            ]);

            // Create and Store Groups
            $groups = [];
            for ($i = 1; $i <= $validatedData['group']; $i++) {
                $groups[] = GameGroup::create([
                    'schedule_id' => $schedule->id,
                    'name' => "Group {$i}",
                ]);
            }

            // Create and Store Courts
            $courts = [];
            for ($i = 1; $i <= $validatedData['court']; $i++) {
                $courts[] = GameArea::create([
                    'schedule_id' => $schedule->id,
                    'name' => "{$matchArea} {$i}",
                ]);
            }
    
             // Generate Teams & Assign Court & Group
            $teams = [];
            for ($team = 1; $team <= $validatedData['team']; $team++) {
                $courtIndex = ($team - 1) % count($courts); // Assign courts in a round-robin manner
                $groupIndex = ($team - 1) % count($groups); // Assign groups in a round-robin manner

                $teams[] = GameTeam::create([
                    'schedule_id' => $schedule->id,
                    'name' => "{$participant} {$team}",
                    'team_number' => $team,
                    'play_area_id' => $courts[$courtIndex]->id,
                    'play_group_id' => $groups[$groupIndex]->id,
                ]);
            }
    
           // Generate Matches Based on Tournament Type
            if ($validatedData['type'] === 'league-round-robin') {
                $this->generateRoundRobin($schedule->id, $teams, $courts, $groups);
            } elseif ($validatedData['type'] === 'knockout-tournament') {
                $this->generateKnockout($schedule->id, $teams, $category->type, $courts, $groups);
            } elseif ($validatedData['type'] === 'league-cum-knockout') {
                $this->generateLeagueCumKnockout($schedule->id, $teams, $category->type, $courts, $groups);
            }
    
            DB::commit();
            return redirect()->route('user.schedule.list')->with('success', 'Schedule created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong! ' . $e->getMessage());
        }
    }
    
    private function generateRoundRobin($scheduleId, $teams, $courts, $groups)
    {
        $teamCount = count($teams);
        if ($teamCount % 2 !== 0) {
            $teams[] = (object)['id' => null, 'name' => null, 'play_area_id' => null, 'play_group_id' => null]; // Bye
            $teamCount++;
        }

        $rounds = $teamCount - 1;
        $matches = [];
        $fixedTeam = array_shift($teams);
        $rotatingTeams = $teams;

        for ($round = 1; $round <= $rounds; $round++) {
            for ($i = 0; $i < $teamCount / 2; $i++) {
                $team1 = ($i == 0) ? $fixedTeam : $rotatingTeams[$i - 1];
                $team2 = $rotatingTeams[$teamCount - 2 - $i];

                $matches[] = [
                    'schedule_id' => $scheduleId,
                    'round' => $round,
                    'team1_id' => $team1->id,
                    'team2_id' => $team2->id,
                    'match_status' => 'pending',
                    'play_area_id' => $team1->play_area_id,
                    'play_group_id' => $team1->play_group_id,
                    'team1_placeholder' => $team1->id ? null : "Bye",
                    'team2_placeholder' => $team2->id ? null : "Bye",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            array_unshift($rotatingTeams, array_pop($rotatingTeams));
        }

        GameMatch::insert($matches);
    }

    private function generateKnockout($scheduleId, $teams, $matchType, $courts, $groups)
    {
        shuffle($teams);
        $matches = [];
        $round = 1;
        $matchCounter = 1;

        for ($i = 0; $i < count($teams); $i += 2) {
            $courtIndex = ($matchCounter - 1) % count($courts);
            $groupIndex = ($matchCounter - 1) % count($groups);

            $match = GameMatch::create([
                'schedule_id' => $scheduleId,
                'team1_id' => $teams[$i]->id,
                'team2_id' => $teams[$i + 1]->id ?? null, // Bye case
                'round' => $round,
                'match_number' => $matchCounter,
                'match_status' => 'pending',
                'play_area_id' => $courts[$courtIndex]->id,
                'play_group_id' => $groups[$groupIndex]->id,
            ]);

            $matches[] = $match;
            $matchCounter++;
        }

        // Generate placeholder matches for next rounds
        $currentMatches = $matches;
        while (count($currentMatches) > 1) {
            $round++;
            $nextMatches = [];
            $matchCounter = 1;

            for ($i = 0; $i < count($currentMatches); $i += 2) {
                $courtIndex = ($matchCounter - 1) % count($courts);
                $groupIndex = ($matchCounter - 1) % count($groups);

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
                    'play_area_id' => $courts[$courtIndex]->id,
                    'play_group_id' => $groups[$groupIndex]->id,
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
                    $match->play_area_id = $previousMatch1->play_area_id; // Preserve Court
                    $match->play_group_id = $previousMatch1->play_group_id; // Preserve Group
                }
            }
    
            if ($match->previous_match2_id) {
                $previousMatch2 = GameMatch::find($match->previous_match2_id);
                if ($previousMatch2 && $previousMatch2->winner_id) {
                    $match->team2_id = $previousMatch2->winner_id;
                    $match->team2_placeholder = null;
                    if (!$match->play_area_id) {
                        $match->play_area_id = $previousMatch2->play_area_id; // Assign only if not set
                    }
                    if (!$match->play_group_id) {
                        $match->play_group_id = $previousMatch2->play_group_id; // Assign only if not set
                    }
                }
            }
    
            $match->save();
        }
    }    
    private function generateLeagueCumKnockout($scheduleId, $teams, $matchType, $courts, $groups)
    {
        // Generate round-robin matches first
        $this->generateRoundRobin($scheduleId, $teams, $courts, $groups);

        // Get top teams for knockout phase
        $qualifiedTeams = $this->getQualifiedTeams($scheduleId, count($groups));

        // Generate knockout matches with placeholders
        $this->generateKnockout($scheduleId, $qualifiedTeams, $matchType, $courts, $groups);
    }

    private function getQualifiedTeams($scheduleId, $groupCount)
    {
        $totalTeams = GameTeam::where('schedule_id', $scheduleId)->count();
        $powerOfTwo = pow(2, floor(log($totalTeams, 2))); // Get largest power of 2 ≤ totalTeams

        if ($groupCount > 1) {
            // Get top teams from each group to balance the knockout stage
            return GameTeam::where('schedule_id', $scheduleId)
                ->orderBy('play_group_id') // Ensure grouping is maintained
                ->orderBy('points', 'desc') // Sort within each group by points
                ->take($powerOfTwo)
                ->get();
        } else {
            // Standard qualification without groups
            return GameTeam::where('schedule_id', $scheduleId)
                ->orderBy('points', 'desc')
                ->take($powerOfTwo)
                ->get();
        }
    }

   


}
