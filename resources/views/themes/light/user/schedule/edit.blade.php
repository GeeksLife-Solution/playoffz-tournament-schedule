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
                                                <tr>
                                                    <td>{{ $matchType }} {{ $loop->iteration }}</td>
                                                    <td>{{ $match->getTeam1Name() }}</td>
                                                    <td>{{ $match->getTeam2Name() }}</td>
                                                    <td>{{ $match->team1_score }} - {{ $match->team2_score }}</td>
                                                    <td>
                                                        @if ($match->isEditable())
                                                        <button class="btn btn-sm btn-warning edit-match-btn"
                                                            data-bs-toggle="modal" data-bs-target="#editMatchModal"
                                                            data-player1-name="{{ $match->getTeam1Name() }}"
                                                            data-player2-name="{{ $match->getTeam2Name() }}"
                                                            data-player1-score="{{ $match->team1_score ?? 0 }}"
                                                            data-player2-score="{{ $match->team2_score ?? 0 }}"
                                                            data-play-group-id="{{ $match->play_group_id }}"
                                                            data-play-area-id="{{ $match->play_area_id }}"
                                                            data-play-group-name="{{ $match->playGroup->name ?? '' }}"
                                                            data-play-area-name="{{ $match->playArea->name ?? '' }}"
                                                            data-team1-avatar="{{ $match->team1 && $match->team1->avatar ? asset($match->team1->avatar) : '' }}"
                                                            data-team2-avatar="{{ $match->team2 && $match->team2->avatar ? asset($match->team2->avatar) : '' }}"
                                                            data-date="{{ $match->match_date ?? '' }}"
                                                            data-time="{{ $match->match_time ?? '' }}"
                                                            data-match-id="{{ $match->id }}">
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
        {{-- <div class="modal fade" id="editMatchModal" tabindex="-1" aria-labelledby="editMatchModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMatchModalLabel">Edit Match</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editMatchForm" action="{{ route('user.update.match') }}" method="POST">
                        @csrf
                        <input type="hidden" name="match_id" id="match_id">
                        <input type="hidden" name="play_group_id" id="play_group_id"> <!-- Hidden field for group ID -->
                        <input type="hidden" name="play_area_id" id="play_area_id"> <!-- Hidden field for area ID -->
                        <div class="modal-body row">
                            <!-- Team 1 Name -->
                            <div class="col-lg-6 mb-3">
                                <label for="team1_name" class="form-label">Team 1 Name</label>
                                <input type="text" class="form-control" id="team1_name" name="team1_name" required>
                            </div>
                            
                            <!-- Team 2 Name -->
                            <div class="col-lg-6 mb-3">
                                <label for="team2_name" class="form-label">Team 2 Name</label>
                                <input type="text" class="form-control" id="team2_name" name="team2_name" required>
                            </div>

                            <!-- Team 1 Score -->
                            <div class="col-lg-6 mb-3">
                                <label for="team1_score" class="form-label">Team 1 Score</label>
                                <input type="number" class="form-control" id="team1_score" name="team1_score" min="0" required>
                            </div>

                            <!-- Team 2 Score -->
                            <div class="col-lg-6 mb-3">
                                <label for="team2_score" class="form-label">Team 2 Score</label>
                                <input type="number" class="form-control" id="team2_score" name="team2_score" min="0" required>
                            </div>

                            <!-- Group Name -->
                            <div class="col-lg-6 mb-3">
                                <label for="play_group_name" class="form-label">Group Name</label>
                                <input type="text" class="form-control" id="play_group_name" name="play_group_name" required>
                            </div>

                            <!-- Area Name -->
                            <div class="col-lg-6 mb-3">
                                <label for="play_area_name" class="form-label">Court Name</label>
                                <input type="text" class="form-control" id="play_area_name" name="play_area_name" required>
                            </div>

                            <!-- Match Date -->
                            <div class="col-lg-6 mb-3">
                                <label for="match_date" class="form-label">Match Date</label>
                                <input type="date" class="form-control" id="match_date" name="match_date" required>
                            </div>

                            <!-- Match Time -->
                            <div class="col-lg-6 mb-3">
                                <label for="match_time" class="form-label">Match Time</label>
                                <input type="time" class="form-control" id="match_time" name="match_time" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}

       <!-- Edit Match Modal -->
        <div class="modal fade" id="editMatchModal" tabindex="-1" aria-labelledby="editMatchModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMatchModalLabel">Edit Match</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editMatchForm" action="{{ route('user.update.match') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="match_id" id="match_id">
                        <input type="hidden" name="play_group_id" id="play_group_id">
                        <input type="hidden" name="play_area_id" id="play_area_id">
                        <div class="modal-body row">
                            <!-- Team 1 Section -->
                            <div class="col-lg-6 mb-3">
                                <label for="team1_name" class="form-label">Team 1 Name</label>
                                <input type="text" class="form-control" id="team1_name" name="team1_name" required>
                                
                                <label for="team1_avatar" class="form-label mt-2">Team 1 Avatar</label>
                                <input type="file" class="form-control" id="team1_avatar" name="team1_avatar" accept="image/*">
                                <div class="mt-2">
                                    <img id="team1_avatar_preview" src="" alt="Team 1 Avatar" style="max-width: 100px; display: none;">
                                </div>
                            </div>
                            
                            <!-- Team 2 Section -->
                            <div class="col-lg-6 mb-3">
                                <label for="team2_name" class="form-label">Team 2 Name</label>
                                <input type="text" class="form-control" id="team2_name" name="team2_name" required>
                                
                                <label for="team2_avatar" class="form-label mt-2">Team 2 Avatar</label>
                                <input type="file" class="form-control" id="team2_avatar" name="team2_avatar" accept="image/*">
                                <div class="mt-2">
                                    <img id="team2_avatar_preview" src="" alt="Team 2 Avatar" style="max-width: 100px; display: none;">
                                </div>
                            </div>

                            <!-- Team 1 Score -->
                            <div class="col-lg-6 mb-3">
                                <label for="team1_score" class="form-label">Team 1 Score</label>
                                <input type="number" class="form-control" id="team1_score" name="team1_score" min="0" required>
                            </div>

                            <!-- Team 2 Score -->
                            <div class="col-lg-6 mb-3">
                                <label for="team2_score" class="form-label">Team 2 Score</label>
                                <input type="number" class="form-control" id="team2_score" name="team2_score" min="0" required>
                            </div>

                            <!-- Group Name -->
                            <div class="col-lg-6 mb-3">
                                <label for="play_group_name" class="form-label">Group Name</label>
                                <input type="text" class="form-control" id="play_group_name" name="play_group_name" required>
                            </div>

                            <!-- Area Name -->
                            <div class="col-lg-6 mb-3">
                                <label for="play_area_name" class="form-label">Court Name</label>
                                <input type="text" class="form-control" id="play_area_name" name="play_area_name" required>
                            </div>

                            <!-- Match Date -->
                            <div class="col-lg-6 mb-3">
                                <label for="match_date" class="form-label">Match Date</label>
                                <input type="date" class="form-control" id="match_date" name="match_date" required>
                            </div>

                            <!-- Match Time -->
                            <div class="col-lg-6 mb-3">
                                <label for="match_time" class="form-label">Match Time</label>
                                <input type="time" class="form-control" id="match_time" name="match_time" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection

    @push('script')
    <script>
     document.addEventListener("DOMContentLoaded", function () {
        // Edit button click handler
        document.querySelectorAll(".edit-match-btn").forEach(button => {
            button.addEventListener("click", function () {
                document.getElementById("match_id").value = this.dataset.matchId;
                document.getElementById("team1_name").value = this.dataset.player1Name;
                document.getElementById("team2_name").value = this.dataset.player2Name;
                document.getElementById("team1_score").value = this.dataset.player1Score;
                document.getElementById("team2_score").value = this.dataset.player2Score;
                document.getElementById("play_group_id").value = this.dataset.playGroupId;
                document.getElementById("play_area_id").value = this.dataset.playAreaId;
                document.getElementById("play_group_name").value = this.dataset.playGroupName || '';
                document.getElementById("play_area_name").value = this.dataset.playAreaName || '';
                document.getElementById("match_date").value = this.dataset.date;
                document.getElementById("match_time").value = this.dataset.time;

                // Set avatar previews if they exist
                const team1Avatar = this.dataset.team1Avatar;
                const team2Avatar = this.dataset.team2Avatar;
                
                const team1Preview = document.getElementById('team1_avatar_preview');
                const team2Preview = document.getElementById('team2_avatar_preview');
                
                if (team1Avatar) {
                    team1Preview.src = team1Avatar;
                    team1Preview.style.display = 'block';
                } else {
                    team1Preview.style.display = 'none';
                }
                
                if (team2Avatar) {
                    team2Preview.src = team2Avatar;
                    team2Preview.style.display = 'block';
                } else {
                    team2Preview.style.display = 'none';
                }
            });
        });
        
        // Avatar preview functionality
        document.getElementById('team1_avatar').addEventListener('change', function(e) {
            const preview = document.getElementById('team1_avatar_preview');
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(this.files[0]);
            } else {
                preview.style.display = 'none';
            }
        });
        
        document.getElementById('team2_avatar').addEventListener('change', function(e) {
            const preview = document.getElementById('team2_avatar_preview');
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(this.files[0]);
            } else {
                preview.style.display = 'none';
            }
        });
    });
    </script>
    
    @endpush