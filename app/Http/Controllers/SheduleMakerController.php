<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Venue;
use App\Models\Participant;
use App\Models\Group;
use App\Models\MatchSchedule;
use App\Models\Scoring;
use App\Models\Standing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SheduleMakerController extends Controller
{

    public function scheduleList()
    {
        return view('admin.schedule.list');
    }
    public function scheduleCreate()
    {
        return view('admin.schedule.create');
    }
    public function generateSchedule(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'sport' => 'required|string|max:100',
            'num_players' => 'required|integer|min:1',
            'schedule_type' => 'required|in:Knockout,League,Round Robin,Swiss',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'venues' => 'required|array',
            'venues.*.venue_name' => 'required|string|max:255',
            'venues.*.venue_type' => 'required|in:Court,Lane,Ground,Table,Track,Other',
        ]);

        // Start a database transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // 1. Create Tournament Schedule
            $schedule = Schedule::create([
                'sport' => $validated['sport'],
                'num_teams' => $validated['num_players'], // Corrected field name 'num_teams'
                'type_of_schedule' => $validated['schedule_type'], // Corrected field name 'type_of_schedule'
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            // 2. Create Venues
            foreach ($validated['venues'] as $venueData) {
                Venue::create([
                    'schedule_id' => $schedule->id,
                    'venue_name' => $venueData['venue_name'],
                    'venue_type' => $venueData['venue_type'],
                    'location_details' => $venueData['location_details'] ?? null,
                    'availability' => true, // Default availability set to true
                ]);
            }

            // 3. Create Participants (dummy participants as example)
            for ($i = 1; $i <= $validated['num_players']; $i++) {
                Participant::create([
                    'schedule_id' => $schedule->id,
                    'participant_name' => "Player $i", // Replace with real player name logic
                    'is_team' => false, // Assuming individual player for simplicity
                ]);
            }

            // 4. Generate Group stages if needed (e.g., League or Round Robin)
            if (in_array($validated['schedule_type'], ['League', 'Round Robin'])) {
                $group = Group::create([
                    'schedule_id' => $schedule->id,
                    'group_name' => 'A',
                ]);

                // Ensure the group was created successfully
                $groupId = $group ? $group->id : null;
            }

            // 5. Generate Match Fixtures
            $participants = Participant::where('schedule_id', $schedule->id)->get();
            $rounds = $this->generateRounds($participants, $validated['schedule_type'], $groupId);
            foreach ($rounds as $round) {
                foreach ($round['matches'] as $match) {
                    $matchSchedule = MatchSchedule::create([
                        'schedule_id' => $schedule->id,
                        'round_number' => $round['round_number'],
                        'group_id' => $round['group_id'] ?? null, // Corrected field name 'group_id'
                        'participant1_id' => $match['participant1_id'],
                        'participant2_id' => $match['participant2_id'],
                        'match_date' => $round['date'], // You can customize the match date logic
                        'match_time' => $round['time'], // You can customize the match time logic
                        'venue_id' => $this->getAvailableVenue($schedule->id)->id, // Assign available venue
                    ]);

                    // Initially, set scores to 0 for each match
                    Scoring::create([
                        'match_id' => $matchSchedule->id,
                        'participant1_score' => 0,
                        'participant2_score' => 0,
                        'winner_id' => null, // No winner initially
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

            return response()->json(['message' => 'Tournament created successfully', 'schedule' => $schedule], 200);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while creating the tournament', 'message' => $e->getMessage()], 500);
        }
    }

    private function generateRounds($participants, $scheduleType, $groupId = null)
    {
        $rounds = [];
        $roundNumber = 1;

        // Round Robin Schedule
        if ($scheduleType == 'Round Robin') {
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
                'group_id' => $groupId,
                'date' => now(), // Assign date dynamically or based on other rules
                'time' => now(), // Assign time dynamically or based on other rules
            ];
        }

        // Knockout Schedule (Single Elimination)
        elseif ($scheduleType == 'Knockout') {
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

        // League Schedule
        elseif ($scheduleType == 'League') {
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

            // For League, each match should be played once
            $rounds[] = [
                'round_number' => $roundNumber,
                'matches' => $matches,
                'group_id' => $groupId,
                'date' => now(),
                'time' => now(),
            ];
        }

        // Swiss Schedule (For balancing groups, not a knockout style)
        elseif ($scheduleType == 'Swiss') {
            $matches = [];
            $roundsCount = ceil(log(count($participants), 2)); // Approximate number of rounds for Swiss

            for ($i = 1; $i <= $roundsCount; $i++) {
                // Pair participants based on previous results or random (example: random for now)
                $randomParticipants = $participants->shuffle();
                for ($j = 0; $j < count($randomParticipants) / 2; $j++) {
                    $matches[] = [
                        'participant1_id' => $randomParticipants[$j * 2]->id,
                        'participant2_id' => $randomParticipants[$j * 2 + 1]->id,
                    ];
                }

                $rounds[] = [
                    'round_number' => $roundNumber++,
                    'matches' => $matches,
                    'date' => now(),
                    'time' => now(),
                ];
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
