@extends($theme . 'layouts.user')
@section('title', trans($title))
@section('content')
    <div class="row justify-content-between ">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="title text-start m-0">@lang('Group List')</h5>
                    <a href="{{route('user.schedule.create')}}" class="btn btn-primary">Create Schedule</a>
                </div>
                <div class="card-body">
                    <div class="row m-0">
                        <div class="col-lg-12">
                            <label for="" class="mb-1">Select Tournament</label>
                            <select name="schedule" id="schedule" class="form-control">
                                <option value="">Choose Any</option>
                                @if(isset($data['schedule']) && $data['schedule']->count() > 0)
                                    @foreach($data['schedule'] as $schedule)
                                        <option value="{{$schedule->id}}">{{$schedule->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                            
                            <div id="schedule-details" class="mt-4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    $(document).ready(function() {
        // AJAX call on schedule change
        $('#schedule').on('change', function() {
            const scheduleId = $(this).val();
            
            if (!scheduleId) {
                $('#schedule-details').html('');
                return;
            }
            
            $.ajax({
                url: '{{ route("user.group.schedule.list", ["id" => ":id"]) }}'.replace(':id', scheduleId),
                type: 'GET',
                beforeSend: function() {
                    $('#schedule-details').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
                },
                success: function(response) {
                    if (response.success) {
                        let html = `
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">${response.data.schedule.name}</h5>
                                </div>
                                <div class="card-body p-0">`;
                        
                        // Group matches by play_group_id
                        const groupedMatches = {};
                        response.data.schedule.game_match.forEach(match => {
                            if (!groupedMatches[match.play_group_id]) {
                                groupedMatches[match.play_group_id] = {
                                    groupName: match.play_group_name,
                                    matches: []
                                };
                            }
                            groupedMatches[match.play_group_id].matches.push(match);
                        });
                        
                        // Create accordion for each group
                        Object.keys(groupedMatches).forEach((groupId, index) => {
                            const group = groupedMatches[groupId];
                            const isFirst = index === 0;
                            
                            html += `
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading${groupId}">
                                        <button class="accordion-button ${isFirst ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${groupId}" aria-expanded="${isFirst ? 'true' : 'false'}" aria-controls="collapse${groupId}">
                                            <strong>${group.groupName || 'Unassigned Group'}</strong>
                                            <span class="badge bg-secondary ms-2">${group.matches.length} matches</span>
                                        </button>
                                    </h2>
                                    <div id="collapse${groupId}" class="accordion-collapse collapse ${isFirst ? 'show' : ''}" aria-labelledby="heading${groupId}" data-bs-parent="#schedule-details">
                                        <div class="accordion-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover mb-0">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Match</th>
                                                            <th>Team A</th>
                                                            <th>Team B</th>
                                                            <th>Score</th>
                                                            <th>Court</th>
                                                            <th>Date</th>
                                                            <th>Time</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>`;
                            
                            // Add matches for this group
                            group.matches.forEach((match, matchIndex) => {
                                const team1Avatar = match.team1_avatar ? `<img src="${match.team1_avatar}" alt="${match.team1_name}" class="avatar-sm rounded-circle me-2">` : '';
                                const team2Avatar = match.team2_avatar ? `<img src="${match.team2_avatar}" alt="${match.team2_name}" class="avatar-sm rounded-circle me-2">` : '';
                                
                                html += `
                                    <tr>
                                        <td>${response.data.matchType} ${matchIndex + 1}</td>
                                        <td>${team1Avatar}${match.team1_name || 'N/A'}</td>
                                        <td>${team2Avatar}${match.team2_name || 'N/A'}</td>
                                        <td><span class="badge bg-primary">${match.team1_score || 0}</span> - <span class="badge bg-danger">${match.team2_score || 0}</span></td>
                                        <td>${match.play_area_name || 'N/A'}</td>
                                        <td>${match.match_date || 'N/A'}</td>
                                        <td>${match.match_time || 'N/A'}</td>
                                        <td>`;
                                
                                if (match.is_editable) {
                                    html += `
                                        <button class="btn btn-sm btn-outline-primary edit-match-btn"
                                            data-bs-toggle="modal" data-bs-target="#editMatchModal"
                                            data-player1-name="${match.team1_name || ''}"
                                            data-player2-name="${match.team2_name || ''}"
                                            data-player1-score="${match.team1_score || 0}"
                                            data-player2-score="${match.team2_score || 0}"
                                            data-play-group-id="${match.play_group_id || ''}"
                                            data-play-area-id="${match.play_area_id || ''}"
                                            data-play-group-name="${match.play_group_name || ''}"
                                            data-play-area-name="${match.play_area_name || ''}"
                                            data-team1-avatar="${match.team1_avatar || ''}"
                                            data-team2-avatar="${match.team2_avatar || ''}"
                                            data-date="${match.match_date || ''}" 
                                            data-time="${match.match_time || ''}"
                                            data-match-id="${match.id}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>`;
                                } else {
                                    html += `<button class="btn btn-sm btn-outline-secondary" disabled><i class="fas fa-lock"></i> Locked</button>`;
                                }
                                
                                html += `</td></tr>`;
                            });
                            
                            html += `</tbody></table></div></div></div></div>`;
                        });
                        
                        html += `</div></div>`;
                        
                        $('#schedule-details').html(html);
                        
                        // Re-attach event handlers for dynamically loaded edit buttons
                        attachEditButtonHandlers();
                    } else {
                        $('#schedule-details').html('<div class="alert alert-danger">Tournament not found</div>');
                    }
                },
                error: function(xhr) {
                    $('#schedule-details').html('<div class="alert alert-danger">Error loading tournament details</div>');
                }
            });
        });
        
        function attachEditButtonHandlers() {
            // Edit button click handler for dynamically loaded content
            $(document).off('click', '.edit-match-btn').on('click', '.edit-match-btn', function() {
                const $button = $(this);
                $('#match_id').val($button.data('match-id'));
                $('#team1_name').val($button.data('player1-name'));
                $('#team2_name').val($button.data('player2-name'));
                $('#team1_score').val($button.data('player1-score'));
                $('#team2_score').val($button.data('player2-score'));
                $('#play_group_id').val($button.data('play-group-id'));
                $('#play_area_id').val($button.data('play-area-id'));
                $('#play_group_name').val($button.data('play-group-name'));
                $('#play_area_name').val($button.data('play-area-name'));
                $('#match_date').val($button.data('date'));
                $('#match_time').val($button.data('time'));
                
                // Set avatar previews
                const team1Avatar = $button.data('team1-avatar');
                const team2Avatar = $button.data('team2-avatar');
                
                const $team1Preview = $('#team1_avatar_preview');
                const $team2Preview = $('#team2_avatar_preview');
                
                if (team1Avatar) {
                    $team1Preview.attr('src', team1Avatar).show();
                } else {
                    $team1Preview.hide();
                }
                
                if (team2Avatar) {
                    $team2Preview.attr('src', team2Avatar).show();
                } else {
                    $team2Preview.hide();
                }
            });
        }
        
        // Avatar preview functionality
        $('#team1_avatar').change(function(e) {
            const preview = $('#team1_avatar_preview');
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.attr('src', e.target.result).show();
                }
                reader.readAsDataURL(this.files[0]);
            } else {
                preview.hide();
            }
        });
        
        $('#team2_avatar').change(function(e) {
            const preview = $('#team2_avatar_preview');
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.attr('src', e.target.result).show();
                }
                reader.readAsDataURL(this.files[0]);
            } else {
                preview.hide();
            }
        });
        
        // Initialize edit button handlers for any static content
        attachEditButtonHandlers();
    });
</script>
<style>
    .avatar-sm {
        width: 30px;
        height: 30px;
        object-fit: cover;
    }
    .accordion-button:not(.collapsed) {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    .table th {
        white-space: nowrap;
    }
</style>
@endpush