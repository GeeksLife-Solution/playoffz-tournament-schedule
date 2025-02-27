@extends($theme.'layouts.user')
@section('title',trans('Create Schedule'))
@section('content')
    <div class="section">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">CreateSchedule</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-sm-auto">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4>Add New Schedule</h4>

                        <form action="{{ route('user.generateSchedule') }}" method="POST">
                            @csrf

                            <div class="form-group mb-3">
                                <label class="text-dark">Tournaments</label>
                                <div class="tom-select-custom">
                                    <select id="tournament_id" name="tournament_id" class="form-control" required>
                                        <option value="">Select Tournament</option>
                                        @foreach($tournaments as $tournament)
                                            <option value="{{ $tournament['event_id']}}">
                                                {{$tournament['event_title']}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('tournament_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="text-dark">Game Categories</label>
                                <div class="tom-select-custom">
                                    <select id="category_id" name="category_id" class="form-control" required>

                                    </select>
                                </div>
                                @error('category_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Number of Players -->
                            <div class="form-group mb-3">
                                <label for="num_players" class="mb-2">Total No. Of Players/Teams</label>
                                <input type="number" min="1" class="form-control" id="num_players" name="num_players">
                                @error('num_players')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Schedule Type -->
                            <div class="form-group mb-3">
                                <label for="schedule_type">Schedule Type</label>
                                <select name="schedule_type" class="form-control" required>
                                    <option value="Knockout_(Single_Elimination)" {{ old('schedule_type') == 'Knockout_(Single_Elimination)' ? 'selected' : '' }}>
                                        Knockout (Single Elimination)
                                    </option>
                                    <option value="Double_Elimination" {{ old('schedule_type') == 'Double_Elimination' ? 'selected' : '' }}>
                                        Double Elimination
                                    </option>
                                    <option value="League_(Round_Robin)" {{ old('schedule_type') == 'League_(Round_Robin)' ? 'selected' : '' }}>
                                        League (Round Robin)
                                    </option>
                                    <option value="League_Cum_Knockout" {{ old('schedule_type') == 'League_Cum_Knockout' ? 'selected' : '' }}>
                                        League Cum Knockout
                                    </option>
                                    <option value="Swiss_System" {{ old('schedule_type') == 'Swiss_System' ? 'selected' : '' }}>
                                        Swiss System
                                    </option>
                                    <option value="Ladder_System" {{ old('schedule_type') == 'Ladder_System' ? 'selected' : '' }}>
                                        Ladder System
                                    </option>
                                </select>
                                @error('schedule_type')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Number of Groups -->
                            <div class="form-group mb-3">
                                <label for="num_groups" class="mb-2">Total No. Of Groups</label>
                                <input type="number" min="1" class="form-control" id="num_groups" name="num_groups">
                                @error('num_groups')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Number of Courts -->
                            <div class="form-group mb-3">
                                <label for="num_courts" class="mb-2">Total No. Of Courts</label>
                                <input type="number" min="1" class="form-control" id="num_courts" name="num_courts">
                                @error('num_courts')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sets Per Match -->
                            <div class="form-group mb-3">
                                <label for="sets_per_match" class="mb-2">Sets Per Match</label>
                                <input type="number" min="1" class="form-control" id="sets_per_match" name="sets_per_match">
                                @error('sets_per_match')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>


                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary btn-sm mt-3">Create Tournament</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function () {
            // Click on "Import Excel" text to open file input
            $('#importExcel').on('click', function () {
                $('#excelFile').click();
            });

            $('#excelFile').on('change', function (event) {
                let file = event.target.files[0];
                if (!file) return;

                let reader = new FileReader();
                reader.readAsBinaryString(file);

                reader.onload = function (e) {
                    let data = e.target.result;
                    let workbook = XLSX.read(data, {type: 'binary'});

                    let sheetName = workbook.SheetNames[0]; // Read the first sheet
                    let sheet = workbook.Sheets[sheetName];
                    let jsonData = XLSX.utils.sheet_to_json(sheet); // Convert to JSON

                    console.log(jsonData); // Log data for debugging

                    // Clear previous options
                    $('#num_players').empty();

                    let selectedValues = [];

                    jsonData.forEach((row) => {
                        if (row.PlayerName) { // Ensure this matches Excel column
                            $('#num_players').append(`<option value="${row.PlayerName}">${row.PlayerName}</option>`);
                            selectedValues.push(row.PlayerName);
                        }
                    });

                    // Auto-select values
                    $('#num_players').val(selectedValues).trigger('change');

                    // Reinitialize select2 if needed
                    if ($.fn.select2) {
                        $('#num_players').select2();
                    }
                };
            });

            $('#tournament_id').on('change', function () {
                let selectedValue = $(this).val();
                if (!selectedValue) return;

                let tournament = JSON.parse(selectedValue);
                let category_id = tournament.event_cat_id;

                $.get(`/user/ajax/get-categories`, function (data) {
                    data.forEach(d => {
                        let isSelected = d.id == category_id ? 'selected' : '';
                        $('#category_id').append(`<option value='${d.id}' ${isSelected}>${d.title}</option>`);
                    });
                });
            });
        });


        let venueCount = 1;
        $('#add-venue').on('click', function () {
            let venueSection = $('#venues-section');
            let newVenueDiv = `
            <div class="venue border p-3 rounded mb-3 bg-light" id="venue-${venueCount}">
                <div class="d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-danger btn-xs remove-venue" data-id="${venueCount}">Remove</button>
                </div>

                <div class="form-group mb-3">
                    <label for="venue_type">Venue Type</label>
                    <select name="venues[${venueCount}][venue_type]" class="form-control" required>
                        <option value="Court">Court</option>
                        <option value="Lane">Lane</option>
                        <option value="Ground">Ground</option>
                        <option value="Table">Table</option>
                        <option value="Track">Track</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            `;
            venueSection.append(newVenueDiv);
            venueCount++;
        });

        $(document).on('click', '.remove-venue', function () {
            let venueId = $(this).data('id');
            $('#venue-' + venueId).remove();
        });
    </script>
@endpush
