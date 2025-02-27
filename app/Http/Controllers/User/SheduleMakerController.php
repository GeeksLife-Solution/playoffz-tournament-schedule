<?php

namespace App\Http\Controllers\User;

use App\Models\Schedule;
use App\Models\Venue;
use App\Models\Participant;
use App\Models\Group;
use App\Models\MatchSchedule;
use App\Models\Scoring;
use App\Models\Standing;
use App\Services\PlayoffzApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SheduleMakerController extends Controller
{

    protected $playOffzApiService;

    public function __construct(PlayoffzApiService $playOffzApiService)
    {
        $this->playOffzApiService = $playOffzApiService;
    }

    public function scheduleList()
    {
        $data['schedules'] = Schedule::all();
        return view('themes.light.user.schedule.list', $data);
    }
    public function scheduleCreate()
    {
        $data['tournaments'] = collect($this->playOffzApiService->getEvents());
        return view('themes.light.user.schedule.create', $data);
    }

    public function getCategories()
    {
        $categories = $this->playOffzApiService->getCategories();
        return response()->json($categories);
    }

    public function scheduleEdit($id)
    {
        $data['schedule'] = Schedule::with(['venues', 'participants', 'matchSchedules', 'standings', 'groups'])->find($id);
        return view('themes.light.user.schedule.edit', $data);
    }

    public function generateSchedule(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'tournament_id' => ['required', 'integer'],
            'category_id' => ['required', 'integer'],
            'num_players' => ['nullable', 'integer', 'min:1'],
            'num_groups' => [
                Rule::requiredIf(function () {
                    return in_array(request('tournament_type'), ['League_Cum_Knockout']);
                }),
                'nullable',
                'integer',
                'min:1',
            ],
            'schedule_type' => 'required|in:Knockout_(Single_Elimination),Double_Elimination,League_(Round_Robin),League_Cum_Knockout,Swiss_System,Ladder_System',
            'num_courts' => ['nullable', 'integer', 'min:1'],
            'sets_per_match' => ['nullable', 'integer', 'min:1'],
        ]);

        // Start a database transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // 1. Create Tournament Schedule
            $schedule = Schedule::create([
                'sport' => $validated['tournament_id'],
                'num_teams' => $validated['num_players'],
                'type_of_schedule' => $validated['schedule_type'],
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now(),
            ]);

            // 3. Create Participants (dummy participants as example)
            $participants = [];
            for ($i = 1; $i <= $validated['num_players']; $i++) {
                $participant = Participant::create([
                    'schedule_id' => $schedule->id,
                    'participant_name' => "Player $i",
                    'is_team' => false,
                ]);
                $participants[] = $participant;
            }

            // 4. Generate Group stages if needed (e.g., League or Round Robin)
            if (in_array($validated['schedule_type'], ['League_Cum_Knockout'])) {
                $group = Group::create([
                    'schedule_id' => $schedule->id,
                    'group_name' => 'A',
                ]);

                // Ensure the group was created successfully
                $groupId = $group ? $group->id : null;
            }

            // Generate Knockout Matches
            $rounds = $this->generateRounds(collect($participants), $validated['schedule_type']);

            foreach ($rounds as $round) {
                foreach ($round['matches'] as $match) {
                    $matchSchedule = MatchSchedule::create([
                        'schedule_id' => $schedule->id,
                        'round_number' => $round['round_number'],
                        'participant1_id' => $match['participant1_id'],
                        'participant2_id' => $match['participant2_id'],
                        'match_date' => $round['date'],
                        'match_time' => $round['time']
                    ]);

                    // Initialize scoring
                    Scoring::create([
                        'match_id' => $matchSchedule->id,
                        'participant1_score' => 0,
                        'participant2_score' => 0,
                        'winner_id' => null,
                    ]);
                }
            }

            // 6. Create Standing
            foreach ($participants as $participant) {
                Standing::create([
                    'schedule_id' => $schedule->id,
                    'participant_id' => $participant->id,
                    'matches_played' => 0,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                    'points' => 0,
                ]);
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('user.listSchedule', $schedule->id)->with('success', 'Successfully Schedule Created.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    private function generateRounds($participants, $scheduleType)
    {
        $rounds = [];
        $roundNumber = 1;

        // Knockout Schedule (Single Elimination)
        if ($scheduleType == 'Knockout_(Single_Elimination)') {
            $shuffled = $participants->shuffle();  // Randomize participants
            $remainingParticipants = $shuffled->values();  // Reset keys

            $numPlayers = count($remainingParticipants);
            $nextPowerOfTwo = pow(2, ceil(log($numPlayers, 2))); // Next power of 2 greater than or equal to numPlayers
            $byes = $nextPowerOfTwo - $numPlayers; // Calculate how many byes are needed

            // Keep track of who advances to the next round
            $advancingParticipants = collect();

            while ($remainingParticipants->count() > 1 || $advancingParticipants->count() > 1) {
                $matches = [];

                // If we're starting a new round, use the advancing participants from previous round
                if ($roundNumber > 1 && $advancingParticipants->count() > 0) {
                    $remainingParticipants = $advancingParticipants->values();
                    $advancingParticipants = collect();
                }

                // Handle byes in the first round
                if ($roundNumber == 1 && $byes > 0) {
                    for ($i = 0; $i < $byes; $i++) {
                        // Players with byes advance automatically
                        $advancingParticipants->push($remainingParticipants->shift());
                    }
                }

                // Create matches for current round
                while ($remainingParticipants->count() > 1) {
                    $p1 = $remainingParticipants->shift();
                    $p2 = $remainingParticipants->shift();

                    $matches[] = [
                        'participant1_id' => $p1->id,
                        'participant2_id' => $p2->id,
                    ];

                    // For demonstration, assume first player wins and advances
                    // In a real system, this would be determined after the match is played
                    $advancingParticipants->push($p1);
                }

                // If there's an odd player left without a match
                if ($remainingParticipants->count() == 1) {
                    $advancingParticipants->push($remainingParticipants->shift());
                }

                // Store the round details
                $rounds[] = [
                    'round_number' => $roundNumber,
                    'matches' => $matches,
                    'date' => '2025-02-26',  // Static date
                    'time' => '10:00:00',    // Static time
                ];

                // Increment round number for next iteration
                $roundNumber++;

                // If only one participant remains, they're the champion
                if ($advancingParticipants->count() <= 1) {
                    break;
                }
            }
        }

        // Double Elimination Tournament
        else if ($scheduleType == 'Double_Elimination') {
            $shuffled = $participants->shuffle();  // Randomize participants
            $numPlayers = count($shuffled);

            // Initialize brackets
            $winnersBracket = $shuffled->values();  // Reset keys
            $losersBracket = collect();
            $eliminatedParticipants = collect();

            // Track winners bracket rounds
            $winnersBracketRounds = [];
            $round = 1;
            $currentWinnersRound = collect($winnersBracket);

            // Generate all winners bracket rounds
            while ($currentWinnersRound->count() > 1) {
                $matchesThisRound = [];
                $nextRound = collect();
                $losersThisRound = collect();

                // Create matches for this round
                while ($currentWinnersRound->count() >= 2) {
                    $p1 = $currentWinnersRound->shift();
                    $p2 = $currentWinnersRound->shift();

                    $matchesThisRound[] = [
                        'participant1_id' => $p1->id,
                        'participant2_id' => $p2->id,
                        'bracket' => 'winners',
                        'round_in_bracket' => $round
                    ];

                    // For demonstration, P1 always wins
                    $nextRound->push($p1);
                    $losersThisRound->push($p2);
                }

                // Handle odd participant (gets a bye)
                if ($currentWinnersRound->count() == 1) {
                    $nextRound->push($currentWinnersRound->shift());
                }

                // Store this round's info
                $winnersBracketRounds[] = [
                    'round' => $round,
                    'matches' => $matchesThisRound,
                    'losers' => $losersThisRound
                ];

                // Set up for next round
                $currentWinnersRound = $nextRound;
                $round++;
            }

            // Final winner
            $winnersBracketChampion = $currentWinnersRound->first();

            // Generate losers bracket rounds
            $losersBracketRounds = [];
            $round = 1;
            $currentLosersRound = collect();

            // Add first round of losers from winners bracket
            foreach ($winnersBracketRounds as $wbRound) {
                // In standard double elimination, losers drop in a specific pattern
                foreach ($wbRound['losers'] as $loser) {
                    $losersBracket->push($loser);
                }
            }

            // Process losers bracket until we have a champion
            $currentLosersRound = $losersBracket;
            while ($currentLosersRound->count() > 1) {
                $matchesThisRound = [];
                $nextRound = collect();

                // Create matches for this round
                while ($currentLosersRound->count() >= 2) {
                    $p1 = $currentLosersRound->shift();
                    $p2 = $currentLosersRound->shift();

                    $matchesThisRound[] = [
                        'participant1_id' => $p1->id,
                        'participant2_id' => $p2->id,
                        'bracket' => 'losers',
                        'round_in_bracket' => $round
                    ];

                    // For demonstration, P1 always wins
                    $nextRound->push($p1);
                    $eliminatedParticipants->push($p2);
                }

                // Handle odd participant (gets a bye)
                if ($currentLosersRound->count() == 1) {
                    $nextRound->push($currentLosersRound->shift());
                }

                // Store this round's info
                $losersBracketRounds[] = [
                    'round' => $round,
                    'matches' => $matchesThisRound
                ];

                // Set up for next round
                $currentLosersRound = $nextRound;
                $round++;
            }

            // Final loser bracket champion
            $losersBracketChampion = $currentLosersRound->first();

            // Now build the actual rounds array by interleaving the winners and losers brackets
            // Start with the winners bracket rounds
            $roundNumber = 1;
            $rounds = [];

            // Combine all rounds in proper sequence
            $maxRounds = max(count($winnersBracketRounds), count($losersBracketRounds));

            for ($i = 0; $i < $maxRounds; $i++) {
                $roundMatches = [];

                // Add winners bracket matches for this round
                if (isset($winnersBracketRounds[$i])) {
                    $roundMatches = array_merge($roundMatches, $winnersBracketRounds[$i]['matches']);
                }

                // Add losers bracket matches for this round
                if (isset($losersBracketRounds[$i])) {
                    $roundMatches = array_merge($roundMatches, $losersBracketRounds[$i]['matches']);
                }

                if (count($roundMatches) > 0) {
                    $rounds[] = [
                        'round_number' => $roundNumber++,
                        'matches' => $roundMatches,
                        'date' => '2025-02-26',
                        'time' => '10:00:00',
                    ];
                }
            }

            // Add the finals (winners bracket champ vs losers bracket champ)
            if (isset($winnersBracketChampion) && isset($losersBracketChampion)) {
                $finalMatches = [[
                    'participant1_id' => $winnersBracketChampion->id,
                    'participant2_id' => $losersBracketChampion->id,
                    'bracket' => 'finals'
                ]];

                $rounds[] = [
                    'round_number' => $roundNumber++,
                    'matches' => $finalMatches,
                    'date' => '2025-02-26',
                    'time' => '10:00:00',
                ];

                // Add potential "true finals" match
                // In a real system, this would be conditional based on who won the first finals match
                $trueFinalMatches = [[
                    'participant1_id' => $losersBracketChampion->id,  // Assuming loser bracket winner won
                    'participant2_id' => $winnersBracketChampion->id,
                    'bracket' => 'true_finals'
                ]];

                $rounds[] = [
                    'round_number' => $roundNumber++,
                    'matches' => $trueFinalMatches,
                    'date' => '2025-02-26',
                    'time' => '10:00:00',
                ];
            }
        }

        else if ($scheduleType == 'League_(Round_Robin)') {
            $matches = [];
            foreach ($participants as $index1 => $participant1) {
                foreach ($participants as $index2 => $participant2) {
                    if ($index1 < $index2) {
                        $matches[] = [
                            'participant1_id' => $participant1->id,
                            'participant2_id' => $participant2->id,
                        ];
                    }
                }
            }

            $rounds[] = [
                'round_number' => $roundNumber,
                'matches' => $matches,
                'date' => now(),
                'time' => now(),
            ];
        }

        elseif ($scheduleType == 'League_Cum_Knockout') {
            // Create knockout-style matchups (1st round -> 2nd round -> etc.)
            $matches = [];
            $participantPairs = array_chunk($participants->toArray(), 2); // Divide participants into pairs

            foreach ($participantPairs as $pair) {
                $matches[] = [
                    'participant1_id' => $pair[0]['id'],
                    'participant2_id' => $pair[1]['id'],
                ];
            }

            $rounds[] = [
                'round_number' => $roundNumber,
                'matches' => $matches,
                'date' => now(),
                'time' => now(),
            ];

            // Generate subsequent rounds (you can adjust the logic to advance winners)
            while (count($matches) > 1) {
                $roundNumber++;
                $matches = array_chunk($matches, 2); // Divide matches into pairs again (winners)

                $newMatches = [];
                foreach ($matches as $key => $matchPair) {
                    // Assuming each match has a winner, we pair winners for the next round
                    $newMatches[] = [
                        'participant1_id' => isset($matchPair[0]['participant1_id']) ? $matchPair[0]['participant1_id'] : $matchPair[0]['participant2_id'], // Winner or bye participant
                        'participant2_id' => isset($matchPair[1]['participant1_id']) ? $matchPair[1]['participant1_id'] : $matchPair[0]['participant2_id'], // Winner or bye participant
                    ];
                }


                $rounds[] = [
                    'round_number' => $roundNumber,
                    'matches' => $newMatches,
                    'date' => now(),
                    'time' => now(),
                ];

                $matches = $newMatches; // Continue the knockout rounds with new matches

            }
        }

        return $rounds;
    }

    private function getAvailableVenue($scheduleId)
    {
        // Example logic to get an available venue for the match
        return Venue::where('schedule_id', $scheduleId)->where('availability', true)->first();
    }
}
