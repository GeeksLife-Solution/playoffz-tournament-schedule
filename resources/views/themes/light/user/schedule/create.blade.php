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
                                            <option value="{{ json_encode($tournament)}}">
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
                                <label for="num_players" class="mb-2">Players/Teams
                                    <span id="importExcel" style="color: blue; cursor: pointer; text-decoration: underline;">
                                        Import Excel
                                    </span>
                                </label>

                                <!-- Hidden file input -->
                                <input type="file" id="excelFile" accept=".xlsx, .xls" style="display: none;">
                                <select id="num_players" name="num_players" class="form-control select2Tags" multiple
                                        required></select>
                                @error('num_players')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Schedule Type -->
                            <div class="form-group mb-3">
                                <label for="schedule_type">Schedule Type</label>
                                <select name="schedule_type" class="form-control" required>
                                    <option value="Knockout" {{ old('schedule_type') == 'Knockout' ? 'selected' : '' }}>
                                        Knockout
                                    </option>
                                    <option value="League" {{ old('schedule_type') == 'League' ? 'selected' : '' }}>
                                        League
                                    </option>
                                    <option
                                        value="Round Robin" {{ old('schedule_type') == 'Round Robin' ? 'selected' : '' }}>
                                        Round Robin
                                    </option>
                                    <option value="Swiss" {{ old('schedule_type') == 'Swiss' ? 'selected' : '' }}>
                                        Swiss
                                    </option>
                                </select>
                                @error('schedule_type')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Start Date -->
                            <div class="form-group mb-3">
                                <label for="start_date">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                       value="{{ old('start_date') }}" required>
                                @error('start_date')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div class="form-group mb-3">
                                <label for="end_date">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                       value="{{ old('end_date') }}" required>
                                @error('end_date')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Venues Section -->
                            <div id="venues-section" class="border p-3 rounded">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">Venues</h4>
                                    <button type="button" id="add-venue" class="btn btn-danger btn-xs">Add Another
                                        Venue
                                    </button>
                                </div>

                                <div class="venue border p-3 rounded mb-3">
                                    <div class="form-group mb-3">
                                        <label for="venue_type">Venue Type</label>
                                        <select name="venues[0][venue_type]" class="form-control" required>
                                            <option value="Court">Court</option>
                                            <option value="Lane">Lane</option>
                                            <option value="Ground">Ground</option>
                                            <option value="Table">Table</option>
                                            <option value="Track">Track</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        @error('venues.0.venue_type')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
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

                $.get(`/admin/ajax/get-categories`, function (data) {
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
