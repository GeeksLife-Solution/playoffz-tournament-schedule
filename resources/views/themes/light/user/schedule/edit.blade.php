@extends($theme . 'layouts.user')
@section('title', trans($title))
<style>
    /* .tournament-bracket {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    margin-top: 20px;
}

.match {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    position: relative;
}

.team {
    width: 200px;
    padding: 10px;
    text-align: center;
    border: 2px solid #ccc;
    border-radius: 8px;
    background-color: #f8f8f8;
    font-weight: bold;
    position: relative;
}

.team.winner {
    border-color: gold;
    background-color: rgba(255, 215, 0, 0.7);
}

.score {
    float: right;
    font-weight: bold;
    color: #555;
}

.match::before {
    content: "";
    width: 2px;
    height: 40px;
    background-color: #ccc;
    position: absolute;
    left: 50%;
    top: -30px;
}

.match:first-child::before {
    display: none;
} */
    .tournament-bracket {
        display: flex;
        /* flex-direction: column; */
        align-items: center;
        gap: 20px;
        margin-top: 20px;
    }

    .round {
        /* display: flex;
    justify-content: center; */
        gap: 50px;
    }

    .match {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        padding: 10px;
    }

    .team {
        width: 200px;
        padding: 10px;
        text-align: center;
        border: 2px solid #ccc;
        border-radius: 8px;
        background-color: #f8f8f8;
        font-weight: bold;
        margin: 5px 0;
        color: #294056;
        position: relative;
    }

    .team.winner {
        border-color: gold;
        background-color: rgba(255, 215, 0, 0.7);
    }

    .score {
        float: right;
        font-weight: bold;
        color: #555;
    }

    /* Connecting Lines */
    .match::before {
        content: "";
        width: 2px;
        height: 28px;
        background-color: #ccc;
        position: absolute;
        left: 50%;
        top: -14px;
    }

    .match:first-child::before {
        display: none;
    }

    .matchFirst::before {
        display: none;
    }

    .final {
        text-align: center;
        margin-top: 30px;
    }

    .winner-box {
        border: 3px solid gold;
        padding: 15px;
        background-color: rgba(255, 215, 0, 0.2);
        border-radius: 10px;
    }
</style>
@section('content')
    @php 
        $matchType = $data['schedule']->gameCategory->type ?? "Match";
        $matchParticipant = $data['schedule']->gameCategory->participant ?? "Team";
        $matchArea = $data['schedule']->gameCategory->area ?? "Court";
    @endphp
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
                    <div class="mt-3">
                        <div class="card-body">
                            @if($data['schedule']->gameMatch->count() > 0)
                                <div class="card mt-3">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">{{$matchType}} Schedule</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>{{$matchType}}</th>
                                                    <th>{{$matchParticipant}} A</th>
                                                    <th>{{$matchParticipant}} B</th>
                                                    <th>Score</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['schedule']->gameMatch as $match)
                                                @php
                                                    // Check if the match has a winner
                                                    $hasWinner = !is_null($match->winner_id);
                                            
                                                    // Check if the winner has progressed to the next round
                                                    $winnerMoved = \App\Models\GameMatch::where(function($query) use ($match) {
                                                                        $query->where('team1_id', $match->winner_id)
                                                                              ->orWhere('team2_id', $match->winner_id);
                                                                    })->exists();
                                            
                                                    // Lock the match only if it has a winner AND that winner has moved to the next round
                                                    $isEditable = !($hasWinner && $winnerMoved);
                                                @endphp
                                                <tr>
                                                    <td>{{$matchType}} {{$loop->iteration}}</td>
                                                    <td>{{ $match->team1_id == 0 ? $match->team1_placeholder : ($match->team1->name ?? 'BYE') }}</td>
                                                    <td>{{ $match->team2_id == 0 ? $match->team2_placeholder : ($match->team2->name ?? 'BYE') }}</td>
                                                    <td>{{ $match->team1_score }} - {{ $match->team2_score }}</td>
                                                    <td>
                                                        @if ($isEditable)
                                                            <button class="btn btn-sm btn-warning"
                                                                onclick="openEditModal(
                                                                    {{ $match->id }},
                                                                    '{{ isset($match->team1) ? $match->team1->name : '' }}',
                                                                    '{{ isset($match->team2) ? $match->team2->name : '' }}',
                                                                    {{ $match->team1_score ?? 0 }},
                                                                    {{ $match->team2_score ?? 0 }}
                                                                )">
                                                                Edit
                                                            </button>
                                                        @else
                                                            <button class="btn btn-sm btn-secondary" disabled>Locked</button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">No {{$matchType}}s scheduled yet.</div>
                            @endif  
                            <div class="tournament-bracket">
                                @php
                                    // Group matches by round
                                    $matchesByRound = $data['schedule']->gameMatch->groupBy('round');

                                    // Find the last round that has matches
                                    $lastRound = $matchesByRound->keys()->max();

                                    // Find the final match to determine the champion
                                    $finalMatch = $matchesByRound[$lastRound]->first();
                                    $champion = null;
                                    if ($finalMatch && $finalMatch->winner_id) {
                                        $champion = $finalMatch->winner;
                                    }
                                @endphp

                                <div>
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">{{$matchType}} Fixtures</h5>
                                    </div>
                                    <div class="tournament-bracket">
                                        @foreach($matchesByRound as $round => $matches)
                                            <div class="round">
                                                <div class="badge badge-rounded bg-success round-title">Round {{ $round }}</div>
                                                @foreach($matches as $match)
                                                    <div class="match {{$loop->index == 0 ? 'matchFirst' : ''}}">
                                                        <div
                                                            class="team {{ $match->team1_score > $match->team2_score ? 'winner' : '' }}">
                                                            {{ $match->team1_id == 0 ? $match->team1_placeholder : ($match->team1->name ?? 'BYE') }}
                                                            <span class="score">{{ $match->team1_score }}</span>
                                                        </div>
                                                        <div
                                                            class="team {{ $match->team2_score > $match->team1_score ? 'winner' : '' }}">
                                                            {{ $match->team2_id == 0 ? $match->team2_placeholder : ($match->team2->name ?? 'BYE') }}
                                                            <span class="score">{{ $match->team2_score }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach

                                        @if($champion)
                                            <div class="final">
                                                <div class="winner-box">
                                                    <h4 class="mb-0">🏆 <br> Winner {{ $champion->name }}</h4>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>


                           {{-- MATCH SCORING --}}
                            <div class="table-responsive mt-4">
                                <div class="card-header bg-primary text-white w-100">
                                    <h5 class="mb-0">{{$matchType}} Score</h5>
                                </div>
                                <table class="table table-bordered text-center">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="white-space: nowrap;">{{$matchType}} No.</th>
                                            <th style="white-space: nowrap;">Round</th>
                                            <th style="white-space: nowrap;">{{$matchParticipant}} A</th>
                                            <th style="white-space: nowrap;">{{$matchParticipant}} A Score</th>
                                            <th style="white-space: nowrap;">{{$matchParticipant}} B</th>
                                            <th style="white-space: nowrap;">{{$matchParticipant}} B Score</th>
                                            <th style="white-space: nowrap;">Winner</th>
                                            <th style="white-space: nowrap;">Points For (A)</th>
                                            <th style="white-space: nowrap;">Points For (B)</th>
                                            <th style="white-space: nowrap;">Points Difference</th>
                                            <th style="white-space: nowrap;">{{$matchType}} Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['schedule']->gameMatch as $match)
    @php
        // Ensure team1 and team2 exist before accessing their names
        $team1Name = $match->team1 ? $match->team1->name : ($match->team1_placeholder ?? 'BYE');
        $team2Name = $match->team2 ? $match->team2->name : ($match->team2_placeholder ?? 'BYE');

        // Check if scores are available
        $team1Score = isset($match->team1_score) ? $match->team1_score : null;
        $team2Score = isset($match->team2_score) ? $match->team2_score : null;

        // Determine the winner using winner_id from the database
        if (!is_null($team1Score) && !is_null($team2Score)) {
            if ($team1Score > $team2Score) {
                $winner = $team1Name;
            } elseif ($team2Score > $team1Score) {
                $winner = $team2Name;
            } else {
                $winner = 'Draw';
            }
        } else {
            $winner = 'TBD'; // Ensure "TBD" is shown when no scores are entered
        }


        // Calculate points difference safely
        $pointsDifference = ($winner !== 'Draw' && $winner !== 'TBD') ? abs($team1Score - $team2Score) : 'N/A';


        // Highlight the winner only if it's not a draw or TBD
        $highlight = ($match->round === 'Final' && $winner !== 'Draw' && $winner !== 'TBD') ? "<b>{$winner} 🏆</b>" : $winner;
    @endphp

    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>Round {{ $match->round }}</td>
        <td>{{ $team1Name }}</td>                                                
        <td>{{ $team1Score ?? '-' }}</td>
        <td>{{ $team2Name }}</td>
        <td>{{ $team2Score ?? '-' }}</td>
        <td>{!! $highlight !!}</td>
        <td>{{ $team1Score ?? '-' }}</td>
        <td>{{ $team2Score ?? '-' }}</td>
                   
        <td>
            @if ($winner !== 'Draw' && $winner !== 'TBD')
                {{ "+$pointsDifference for $winner" }}
            @else
                N/A
            @endif
        </td>
        
        <td>
            @if (!is_null($match->winner_id) && !is_null($team1Score) && !is_null($team2Score))
                Completed
            @else
                Pending
            @endif
        </td>
        
    </tr>
@endforeach

                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Match Modal -->
        <div class="modal fade" id="editMatchModal" tabindex="-1" role="dialog" aria-labelledby="editMatchModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content p-0">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMatchModalLabel">Edit {{$matchType}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <form method="POST" class="p-0" action="{{ route('user.update.match') }}">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" name="match_id" id="match_id">
                                <div class="form-group mb-2">
                                    <label for="team1_name">{{$matchParticipant}} A</label>
                                    <input type="text" class="form-control" id="team1_name" name="team1_name" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="team2_name">{{$matchParticipant}} B</label>
                                    <input type="text" class="form-control" id="team2_name" name="team2_name" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="team1_score">{{$matchParticipant}} A Score</label>
                                    <input type="number" class="form-control" id="team1_score" name="team1_score" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="team2_score">{{$matchParticipant}} B Score</label>
                                    <input type="number" class="form-control" id="team2_score" name="team2_score" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection

    @push('script')
        <script>
            function openEditModal(matchId, team1Name, team2Name, team1Score, team2Score) {
                document.getElementById('match_id').value = matchId;
                document.getElementById('team1_name').value = team1Name;
                document.getElementById('team2_name').value = team2Name;
                document.getElementById('team1_score').value = team1Score;
                document.getElementById('team2_score').value = team2Score;
                $('#editMatchModal').modal('show');
            }
        </script>
    @endpush