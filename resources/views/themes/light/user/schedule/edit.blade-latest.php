@extends($theme . 'layouts.user')
@section('title', trans($title))
<style>
    .tournament-bracket {
        display: flex;
        align-items: center;
        gap: 40px;
        margin-top: 20px;
        flex-direction: row;
        position: relative;
    }

    .round {
        display: flex;
        flex-direction: column;
        margin: 0px;
        position: relative;
        gap:20px;
    }

    .match {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        padding: 0px;
        margin: 0;
        gap: 5px;
    }

    .team {
        padding: 5px 10px;
        margin: 2px 0;
        width: 160px;
        background: transparent;
        border-radius: 4px;
        position: relative;
        font-size: 14px;
        border: 1px solid #0d6efd;
        color: #fff;
        text-align: center;
    }

    .team.winner {
        border-color: gold;
        background-color: rgba(255, 215, 0, 0.7);
    }

    .score {
        float: right;
        font-weight: bold;
        color: #dcdcdc;
        margin-left: 10px;
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

    /* Connector lines */
    .connector {
        position: absolute;
        top: 50%;
        right: -20px;
        width: 20px;
        height: 2px;
        background: #0d6efd;
        z-index: 1;
    }

    .team:nth-child(1) .connector {
        transform: translateY(-50%);
    }

    .team:nth-child(2) .connector {
        transform: translateY(-50%);
    }
    
    /* Vertical connector for next round */
    .match::after {
        content: '';
        position: absolute;
        top: 18px;
        bottom: 0;
        right: -20px;
        width: 1px;
        background: #0d6efd;
        z-index: 0;
        height: 43px;
    }

    /* Hide connectors for the last round */
    .round:last-child .connector,
    .round:last-child .match::after {
        display: none;
    }

    .final {
        display: flex;
        align-items: center;
        margin-left: 20px;
    }

    .winner-box {
        padding: 20px;
        background: #e8f5e9;
        border-radius: 8px;
        text-align: center;
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
        <div class="col-md-11">
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
                                <div class="mt-3">
                                    <div class="card-header bg-primary text-white w-100">
                                        <h5 class="mb-0">{{$matchType}} Schedule</h5>
                                    </div>
                                    <div class="card-body p-0">
                                       <table class="table table-bordered text-center" style="font-size: 14px; width: 100%;    vertical-align: baseline;">
                                            <thead class="thead-light" style="background-color: #f8f9fa;">
                                                <tr>
                                                    <th style="white-space: nowrap; width: 5%; padding: 5px;color:#000;">{{$matchType}}</th>
                                                    <th style="white-space: nowrap; width: 5%; padding: 5px;color:#000;">{{$matchParticipant}} A</th>
                                                    <th style="white-space: nowrap; width: 5%; padding: 5px;color:#000;">{{$matchParticipant}} B</th>
                                                    <th style="white-space: nowrap; width: 5%; padding: 5px;color:#000;">Score</th>
                                                    <th style="white-space: nowrap; width: 5%; padding: 5px;color:#000;">Group</th>
                                                    <th style="white-space: nowrap; width: 5%; padding: 5px;color:#000;">Court</th>
                                                    <th style="white-space: nowrap; width: 5%; padding: 5px;color:#000;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['schedule']->gameMatch as $match)
                                                <tr>
                                                    <td style="padding: 5px;">{{ $matchType }} {{ $loop->iteration }}</td>
                                                    <td style="padding: 5px;">{{ $match->getTeam1Name() }}</td>
                                                    <td style="padding: 5px;">{{ $match->getTeam2Name() }}</td>
                                                    <td style="padding: 5px;">{{ $match->team1_score }} - {{ $match->team2_score }}</td>
                                                    <td style="padding: 5px;">{{ $match->playGroup->name }}</td>
                                                    <td style="padding: 5px;">{{ $match->playArea->name }}</td>
                                                    <td style="padding: 5px;">
                                                        @if ($match->isEditable())
                                                        <button class="edit-match-btn py-1"
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
                                                            data-date="{{ $match->match_date ?? date('Y-m-d') }}" 
                                                            data-time="{{ $match->match_time ?? date('H:i') }}"
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

                            <div class="mt-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">{{$matchType}} Fixtures</h5>
                                </div>
                                <div class="tournament-bracket">
                                    @foreach($matchesByRound as $round => $matches)
                                        <div class="round">
                                            <!-- <div class="badge badge-rounded bg-success round-title">Round {{ $round }}</div> -->
                                            @foreach($matches as $match)
                                                <div class="match {{$loop->index == 0 ? 'matchFirst' : ''}}">
                                                    <div class="team {{ $match->team1_score > $match->team2_score ? 'winner' : '' }}">
                                                        {{ $match->team1_id == 0 ? $match->team1_placeholder : ($match->team1->name ?? 'BYE') }}
                                                        <span class="score">{{ $match->team1_score }}</span>
                                                        <div class="connector"></div>
                                                    </div>
                                                    <div class="team {{ $match->team2_score > $match->team1_score ? 'winner' : '' }}">
                                                        {{ $match->team2_id == 0 ? $match->team2_placeholder : ($match->team2->name ?? 'BYE') }}
                                                        <span class="score">{{ $match->team2_score }}</span>
                                                        <div class="connector"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach

                                    @if($champion)
                                        <div class="round">
                                            <div class="badge badge-rounded bg-success round-title">Winner</div>
                                            <div class="winner-box">
                                                <h4 class="mb-0">🏆 <br> {{ $champion->name }}</h4>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                           {{-- MATCH SCORING --}}
                            <div class="table-responsive mt-4">
                                <div class="card-header bg-primary text-white w-100">
                                    <h5 class="mb-0">{{$matchType}} Score</h5>
                                </div>
                               <table class="table table-bordered text-center" style="font-size: 14px; width: 100%;">
                                    <thead class="thead-light" style="background-color: #f8f9fa;">
                                        <tr>
                                            <th style="white-space: nowrap; width: 5%; padding: 5px;color:#000;">#</th>
                                            <th style="white-space: nowrap; width: 5%; padding: 5px;color:#000;">Round</th>
                                            <th style="white-space: nowrap; width: 15%; padding: 5px;color:#000;">{{$matchParticipant}} A</th>
                                            <th style="white-space: nowrap; width: 7%; padding: 5px;color:#000;">Score</th>
                                            <th style="white-space: nowrap; width: 15%; padding: 5px;color:#000;">{{$matchParticipant}} B</th>
                                            <th style="white-space: nowrap; width: 7%; padding: 5px;color:#000;">Score</th>
                                            <th style="white-space: nowrap; width: 15%; padding: 5px;color:#000;">Winner</th>
                                            <th style="white-space: nowrap; width: 5%; padding: 5px;color:#000;">Pts A</th>
                                            <th style="white-space: nowrap; width: 5%; padding: 5px;color:#000;">Pts B</th>
                                            <th style="white-space: nowrap; width: 10%; padding: 5px;color:#000;">Pts Diff</th>
                                            <th style="white-space: nowrap; width: 6%; padding: 5px;color:#000;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['schedule']->gameMatch as $match)
                                            @php
                                                $team1Name = $match->team1 ? $match->team1->name : ($match->team1_placeholder ?? 'BYE');
                                                $team2Name = $match->team2 ? $match->team2->name : ($match->team2_placeholder ?? 'BYE');
                                                $team1Score = isset($match->team1_score) ? $match->team1_score : '-';
                                                $team2Score = isset($match->team2_score) ? $match->team2_score : '-';

                                                if (!is_null($match->team1_score) && !is_null($match->team2_score)) {
                                                    if ($match->team1_score > $match->team2_score) {
                                                        $winner = $team1Name;
                                                    } elseif ($match->team2_score > $match->team1_score) {
                                                        $winner = $team2Name;
                                                    } else {
                                                        $winner = 'Draw';
                                                    }
                                                } else {
                                                    $winner = 'TBD';
                                                }

                                                $pointsDifference = ($winner !== 'Draw' && $winner !== 'TBD') ? abs($match->team1_score - $match->team2_score) : 'N/A';
                                                $highlight = ($match->round === 'Final' && $winner !== 'Draw' && $winner !== 'TBD') ? "<b style='color: #d63384;'>{$winner} 🏆</b>" : $winner;
                                                $status = (!is_null($match->winner_id)) ? '<span style="color: #28a745;">✓</span>' : '<span style="color: #dc3545;">Pending</span>';
                                                
                                                $roundDisplay = preg_replace('/^Round /', 'R', $match->round);
                                                
                                                // Row background color alternating
                                                $rowBg = $loop->iteration % 2 === 0 ? 'background-color: #f8f9fa;' : 'background-color: #ffffff;';
                                            @endphp

                                            <tr style="{{ $rowBg }}">
                                                <td style="padding: 5px;">{{ $loop->iteration }}</td>
                                                <td style="padding: 5px;">{{ $roundDisplay }}</td>
                                                <td style="padding: 5px; text-align: left;">{{ $team1Name }}</td>                                                
                                                <td style="padding: 5px; font-weight: bold;">{{ $team1Score }}</td>
                                                <td style="padding: 5px; text-align: left;">{{ $team2Name }}</td>
                                                <td style="padding: 5px; font-weight: bold;">{{ $team2Score }}</td>
                                                <td style="padding: 5px;">{!! $highlight !!}</td>
                                                <td style="padding: 5px;">{{ $team1Score }}</td>
                                                <td style="padding: 5px;">{{ $team2Score }}</td>
                                                <td style="padding: 5px;">
                                                    @if ($winner !== 'Draw' && $winner !== 'TBD')
                                                        <span style="color: #6c757d;">+{{ $pointsDifference }}</span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td style="padding: 5px;">{!! $status !!}</td>
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
        <div class="modal fade" id="editMatchModal" tabindex="-1" aria-labelledby="editMatchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header pb-0">
                        <h5 class="modal-title" id="editMatchModalLabel">Edit Match</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editMatchForm" class="m-0" style="max-width:100%;" action="{{ route('user.update.match') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="match_id" id="match_id">
                        <input type="hidden" name="play_group_id" id="play_group_id">
                        <input type="hidden" name="play_area_id" id="play_area_id">
                        <div class="modal-body px-2 p-0">
                            <div class="row">
                                <!-- Team 1 Section -->
                                <div class="col-lg-6 mb-3">
                                    <label for="team1_name" class="form-label">{{$matchParticipant}} Name</label>
                                    <input type="text" class="form-control" id="team1_name" name="team1_name" required>
                                    
                                    <label for="team1_avatar" class="form-label mt-2">{{$matchParticipant}} Avatar</label>
                                    <input type="file" class="form-control" id="team1_avatar" name="team1_avatar" accept="image/*">
                                    <div class="mt-2">
                                        <img id="team1_avatar_preview" src="" alt="Team 1 Avatar" style="max-width: 100px;display: block;height: 50px;">
                                    </div>
                                </div>
                                
                                <!-- Team 2 Section -->
                                <div class="col-lg-6 mb-3">
                                    <label for="team2_name" class="form-label">{{$matchParticipant}} Name</label>
                                    <input type="text" class="form-control" id="team2_name" name="team2_name" required>
                                    
                                    <label for="team2_avatar" class="form-label mt-2">{{$matchParticipant}} Avatar</label>
                                    <input type="file" class="form-control" id="team2_avatar" name="team2_avatar" accept="image/*">
                                    <div class="mt-2">
                                        <img id="team2_avatar_preview" src="" alt="Team 2 Avatar" style="max-width: 100px;display: block;height: 50px;">
                                    </div>
                                </div>

                                <!-- Team 1 Score -->
                                <div class="col-lg-6 mb-3">
                                    <label for="team1_score" class="form-label">{{$matchParticipant}} Score</label>
                                    <input type="number" class="form-control" id="team1_score" name="team1_score" min="0" required>
                                </div>

                                <!-- Team 2 Score -->
                                <div class="col-lg-6 mb-3">
                                    <label for="team2_score" class="form-label">{{$matchParticipant}} Score</label>
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