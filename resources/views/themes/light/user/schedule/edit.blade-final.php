@extends($theme . 'layouts.user')
@section('title', trans($title))
<style>
   .tournament-bracket-container {
        overflow-x: auto;
        padding: 20px;
        position: relative; /* important for connectors */
        background: #1e354d;
    }

    .tournament-bracket {
        display: flex;
        gap: 20px; /* wider gap for clean connectors */
        align-items: flex-start;
        position: relative;
        padding: 40px;
    }

    .round {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 50px; /* Increased spacing to match visual hierarchy */
        position: relative;
        min-height: 100%; /* Let columns stretch evenly */
    }

    .match {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 10px;
        width: 200px;
        height: 70px; /* Fixed height ensures alignment */
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .team {
        display: flex;
        justify-content: space-between;
        padding: 4px 8px;
        background: #fff;
        margin-bottom: 4px;
        border-radius: 4px;
        color: #000;
    }

    .team.winner {
        font-weight: bold;
        background: #d4edda;
        border-left: 4px solid #28a745;
    }

    .score {
        font-weight: bold;
    }

    .winner-box {
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        background: gold;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }

    .connectors-layer {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .tournament-bracket {
        display: grid;
        grid-auto-flow: column;
        grid-gap: 20px;
        align-items: start;
        padding: 40px;
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

                            <!-- FIXTURE LOGIC -->

                            @php
                                $matchesByRound = $data['schedule']->gameMatch->groupBy('round');
                                $lastRound = $matchesByRound->keys()->max();
                                $finalMatch = $matchesByRound[$lastRound]->first();
                                $champion = $finalMatch && $finalMatch->winner_id ? $finalMatch->winner : null;
                            @endphp

                            <div class="mt-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">{{ $matchType }} Fixtures</h5>
                                </div>

                                <div class="tournament-bracket-container">
                                    <svg id="connectors-layer" class="connectors-layer"></svg>
                                    <div class="tournament-bracket">
                                        @foreach($matchesByRound as $round => $matches)
                                            <div class="round" data-round="{{ $round }}">
                                                <!-- <div class="round-title">Round {{ $round }}</div> -->
                                                @foreach($matches as $match)
                                                    <div class="match" id="match-{{ $match->id }}" data-id="{{ $match->id }}" data-round="{{ $match->round }}">
                                                        <div class="team {{ $match->team1_score > $match->team2_score ? 'winner' : '' }}">
                                                            {{ $match->team1_id == 0 ? $match->team1_placeholder : ($match->team1->name ?? 'BYE') }}
                                                            <span class="score">{{ $match->team1_score }}</span>
                                                        </div>
                                                        <div class="team {{ $match->team2_score > $match->team1_score ? 'winner' : '' }}">
                                                            {{ $match->team2_id == 0 ? $match->team2_placeholder : ($match->team2->name ?? 'BYE') }}
                                                            <span class="score">{{ $match->team2_score }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach


                                        @if($champion)
                                            <div class="round winner-round">
                                                <div class="round-title">🏆 Winner</div>
                                                <div class="winner-box">
                                                    {{ $champion->name }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- FIXTURE LOGIC END -->

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
    <!-- <script>
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
   <script>
    document.addEventListener('DOMContentLoaded', function () {
        const svg = document.getElementById('connectors-layer');
        svg.innerHTML = '';

        const matches = Array.from(document.querySelectorAll('.match'));

        // Group matches by round
        const matchesByRound = {};
        matches.forEach(match => {
            const round = match.dataset.round;
            if (!matchesByRound[round]) {
                matchesByRound[round] = [];
            }
            matchesByRound[round].push(match);
        });

        // Sort rounds numerically
        const sortedRounds = Object.keys(matchesByRound).sort((a, b) => parseInt(a) - parseInt(b));

        // Draw connectors from each round to the next
        for (let i = 0; i < sortedRounds.length - 1; i++) {
            const currentRound = sortedRounds[i];
            const nextRound = sortedRounds[i + 1];

            const currentMatches = matchesByRound[currentRound];
            const nextMatches = matchesByRound[nextRound];

            for (let j = 0; j < currentMatches.length; j += 2) {
                const match1 = currentMatches[j];
                const match2 = currentMatches[j + 1];
                const nextMatch = nextMatches[Math.floor(j / 2)];

                if (match1 && match2 && nextMatch) {
                    drawForkConnector(match1, match2, nextMatch);
                }

            }
        }

        function drawForkConnector(match1, match2, toMatch) {
            const svgRect = svg.getBoundingClientRect();
            const m1Rect = match1.getBoundingClientRect();
            const m2Rect = match2.getBoundingClientRect();
            const toRect = toMatch.getBoundingClientRect();

            // Match 1 exit point
            const startX1 = m1Rect.right - svgRect.left;
            const startY1 = m1Rect.top + m1Rect.height / 2 - svgRect.top;

            // Match 2 exit point
            const startX2 = m2Rect.right - svgRect.left;
            const startY2 = m2Rect.top + m2Rect.height / 2 - svgRect.top;

            // Join point (fork center)
            const midX = (startX1 + startX2) / 2 + 40;
            const midY = (startY1 + startY2) / 2;

            // To match entry point
            const endX = toRect.left - svgRect.left;
            const endY = toRect.top + toRect.height / 2 - svgRect.top;

            // Draw from match1 to fork
            svg.appendChild(createPath(startX1, startY1, midX, midY));
            // Draw from match2 to fork
            svg.appendChild(createPath(startX2, startY2, midX, midY));
            // Draw fork to next match
            svg.appendChild(createPath(midX, midY, endX, endY));
        }

        function createPath(x1, y1, x2, y2) {
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            const dx = Math.abs(x2 - x1) / 2;
            path.setAttribute('d', `M${x1},${y1} C${x1 + dx},${y1} ${x2 - dx},${y2} ${x2},${y2}`);
            path.setAttribute('stroke', '#0dcaf0');
            path.setAttribute('stroke-width', '2');
            path.setAttribute('fill', 'none');
            return path;
        }
    });
</script> -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            drawAllConnectors();
        });
    });

    function drawAllConnectors() {
        const svg = document.getElementById('connectors-layer');
        svg.innerHTML = '';

        const matches = Array.from(document.querySelectorAll('.match'));
        const matchesByRound = {};

        matches.forEach(match => {
            const round = match.dataset.round;
            if (!matchesByRound[round]) matchesByRound[round] = [];
            matchesByRound[round].push(match);
        });

        const sortedRounds = Object.keys(matchesByRound).sort((a, b) => parseInt(a) - parseInt(b));

        for (let i = 0; i < sortedRounds.length - 1; i++) {
            const currentMatches = matchesByRound[sortedRounds[i]];
            const nextMatches = matchesByRound[sortedRounds[i + 1]];

            for (let j = 0; j < currentMatches.length; j += 2) {
                const match1 = currentMatches[j];
                const match2 = currentMatches[j + 1];
                const nextMatch = nextMatches[Math.floor(j / 2)];
                if (match1 && match2 && nextMatch) {
                    drawForkConnector(svg, match1, match2, nextMatch);
                }
            }
        }
    }

    function drawForkConnector(svg, match1, match2, toMatch) {
        const svgRect = svg.getBoundingClientRect();
        const m1Rect = match1.getBoundingClientRect();
        const m2Rect = match2.getBoundingClientRect();
        const toRect = toMatch.getBoundingClientRect();

        const startX1 = m1Rect.right - svgRect.left;
        const startY1 = m1Rect.top + m1Rect.height / 2 - svgRect.top;

        const startX2 = m2Rect.right - svgRect.left;
        const startY2 = m2Rect.top + m2Rect.height / 2 - svgRect.top;

        const midX = (startX1 + startX2) / 2 + 40;
        const midY = (startY1 + startY2) / 2;

        const endX = toRect.left - svgRect.left;
        const endY = toRect.top + toRect.height / 2 - svgRect.top;

        svg.appendChild(createPath(startX1, startY1, midX, midY));
        svg.appendChild(createPath(startX2, startY2, midX, midY));
        svg.appendChild(createPath(midX, midY, endX, endY));
    }

    function createPath(x1, y1, x2, y2) {
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        const dx = Math.abs(x2 - x1) / 2;
        path.setAttribute('d', `M${x1},${y1} C${x1 + dx},${y1} ${x2 - dx},${y2} ${x2},${y2}`);
        path.setAttribute('stroke', '#0dcaf0');
        path.setAttribute('stroke-width', '2');
        path.setAttribute('fill', 'none');
        return path;
    }
});
</script>

@endpush