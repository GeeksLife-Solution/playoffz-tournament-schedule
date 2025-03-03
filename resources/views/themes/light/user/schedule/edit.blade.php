@extends($theme.'layouts.user')
@section('title',trans($title))
@section('content')
    <div class="row justify-content-center ">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="title text-start m-0 text-center">{{$data['schedule']->name}}</h4>
                </div>
                <div class="card-body">
                    <div class="text-center bg-dark">
                        @if (isset($data['schedule']->image))
                            <img src="{{$data['schedule']->image}}" class="img-fluid" alt="{{$data['schedule']->name}}">
                        @endif
                    </div>
                    {{-- <div class="mt-3">
                        <h5 class="mb-0">Schedule</h5>
                        @php $total_team = $data['teams']->type ?? 2; @endphp
                        @if (isset($data['schedule']->type) === "knockout-tournament")
                        @elseif (isset($data['schedule']->type) === "league-round-robin")
                        @elseif (isset($data['schedule']->type) === "league-cum-knockout")
                        @endif
                    </div> --}}
                    <div class="mt-3">
                        <h5 class="mb-0">Schedule</h5>
                    
                        @php 
                            $total_teams = $data['teams']->type ?? 8; // Default 8 teams
                            $teams = [];
                    
                            // Generate sample teams
                            for ($i = 1; $i <= $total_teams; $i++) {
                                $teams[] = "Team $i";
                            }
                    
                            $schedule = [];
                    
                            // Knockout Tournament
                            if ($data['schedule']->type === "knockout-tournament") {
                                $gameNumber = 1;
                                $round = 1;
                                $currentRoundTeams = $teams;
                    
                                while (count($currentRoundTeams) > 1) {
                                    $nextRoundTeams = [];
                    
                                    for ($i = 0; $i < count($currentRoundTeams); $i += 2) {
                                        $teamA = $currentRoundTeams[$i];
                                        $teamB = $currentRoundTeams[$i + 1] ?? "Bye";
                    
                                        $schedule[] = [
                                            'game' => "Game $gameNumber",
                                            'team_a' => $teamA,
                                            'team_b' => $teamB,
                                            'round' => "Round $round"
                                        ];
                    
                                        $nextRoundTeams[] = "Winner Game $gameNumber";
                                        $gameNumber++;
                                    }
                    
                                    $currentRoundTeams = $nextRoundTeams;
                                    $round++;
                                }
                            } 
                            
                            // League Round Robin
                            elseif ($data['schedule']->type === "league-round-robin") {
                                $gameNumber = 1;
                                for ($i = 0; $i < count($teams); $i++) {
                                    for ($j = $i + 1; $j < count($teams); $j++) {
                                        $schedule[] = [
                                            'game' => "Game $gameNumber",
                                            'team_a' => $teams[$i],
                                            'team_b' => $teams[$j],
                                            'round' => "Round Robin"
                                        ];
                                        $gameNumber++;
                                    }
                                }
                            } 
                            
                            // League Cum Knockout
                            elseif ($data['schedule']->type === "league-cum-knockout") {
                                // Step 1: Round Robin for groups
                                $groupA = array_slice($teams, 0, ceil(count($teams) / 2));
                                $groupB = array_slice($teams, ceil(count($teams) / 2));
                    
                                $gameNumber = 1;
                    
                                foreach ([$groupA, $groupB] as $index => $group) {
                                    for ($i = 0; $i < count($group); $i++) {
                                        for ($j = $i + 1; $j < count($group); $j++) {
                                            $schedule[] = [
                                                'game' => "Game $gameNumber",
                                                'team_a' => $group[$i],
                                                'team_b' => $group[$j],
                                                'round' => "Group " . chr(65 + $index) . " Round"
                                            ];
                                            $gameNumber++;
                                        }
                                    }
                                }
                    
                                // Step 2: Knockout for top teams
                                $schedule[] = [
                                    'game' => "Game $gameNumber",
                                    'team_a' => "Top Group A",
                                    'team_b' => "Top Group B",
                                    'round' => "Knockout Final"
                                ];
                            }
                        @endphp
                    
                        @if (!empty($schedule))
                            <table class="table table-bordered mt-3">
                                <thead>
                                    <tr>
                                        <th>Game</th>
                                        <th>Team A</th>
                                        <th>Team B</th>
                                        <th>Round</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedule as $match)
                                    <tr>
                                        <td>{{ $match['game'] }}</td>
                                        <td>{{ $match['team_a'] }}</td>
                                        <td>{{ $match['team_b'] }}</td>
                                        <td>{{ $match['round'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>                    
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
@endpush