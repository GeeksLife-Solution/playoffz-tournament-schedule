@extends('admin.layouts.app')
@section('page_title','Schedule List')
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link" href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Schedule List</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">Schedule List</h1>
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

                        <form action="{{ route('admin.generateSchedule') }}" method="POST">
                            @csrf

                            <!-- Sport -->
                            <div class="form-group mb-3">
                                <label for="sport">Sport</label>
                                <input type="text" name="sport" class="form-control" value="{{ old('sport') }}" required>
                                @error('sport')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Number of Players -->
                            <div class="form-group mb-3">
                                <label for="num_players">Number of Players</label>
                                <input type="number" name="num_players" class="form-control" value="{{ old('num_players') }}" required>
                                @error('num_players')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Schedule Type -->
                            <div class="form-group mb-3">
                                <label for="schedule_type">Schedule Type</label>
                                <select name="schedule_type" class="form-control" required>
                                    <option value="Knockout" {{ old('schedule_type') == 'Knockout' ? 'selected' : '' }}>Knockout</option>
                                    <option value="League" {{ old('schedule_type') == 'League' ? 'selected' : '' }}>League</option>
                                    <option value="Round Robin" {{ old('schedule_type') == 'Round Robin' ? 'selected' : '' }}>Round Robin</option>
                                    <option value="Swiss" {{ old('schedule_type') == 'Swiss' ? 'selected' : '' }}>Swiss</option>
                                </select>
                                @error('schedule_type')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Start Date -->
                            <div class="form-group mb-3">
                                <label for="start_date">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                                @error('start_date')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div class="form-group mb-3">
                                <label for="end_date">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                                @error('end_date')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Venues Section -->
                            <div id="venues-section" class="border p-3 rounded">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">Venues</h4>
                                    <button type="button" id="add-venue" class="btn btn-secondary">Add Another Venue</button>
                                </div>

                                <div class="venue border p-3 rounded mb-3">
                                    <div class="form-group mb-3">
                                        <label for="venue_name">Venue Name</label>
                                        <input type="text" name="venues[0][venue_name]" class="form-control" required>
                                        @error('venues.0.venue_name')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

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
                            <button type="submit" class="btn btn-primary">Create Tournament</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css-lib')
    <!-- Add any additional CSS libraries if needed -->
@endpush

@push('js-lib')
    <!-- Add any additional JS libraries if needed -->
@endpush

@push('script')
    <script>
        let venueCount = 1;

        $('#add-venue').on('click', function() {
            let venueSection = $('#venues-section');
            let newVenueDiv = `
            <div class="venue border p-3 rounded mb-3 bg-light" id="venue-${venueCount}">
                <div class="d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-danger btn-sm remove-venue" data-id="${venueCount}">Remove</button>
                </div>

                <div class="form-group mb-3">
                    <label for="venue_name">Venue Name</label>
                    <input type="text" name="venues[${venueCount}][venue_name]" class="form-control" required>
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

        $(document).on('click', '.remove-venue', function() {
            let venueId = $(this).data('id');
            $('#venue-' + venueId).remove();
        });
    </script>


@endpush
