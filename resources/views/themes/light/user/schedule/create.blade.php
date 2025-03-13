@extends($theme.'layouts.user')
@section('title',trans($title))
@section('content')

    <div class="row justify-content-between ">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-center">
                    <h5 class="title text-start m-0">@lang('Create Schedule')</h5>
                </div>
                <div class="card-body">
                    <p>
                        @lang('For online scheduling and scoring. Or create a new registration activity, waiver, page, or file upload.')</p>
                    <div>
                        <form action="{{route('user.schedule.store')}}" method="POST">
                            @csrf
                            <div class="form-group mb-2">
                                <label for="sport">Sport <span class="text-danger">*</span></label>
                                <div class="input-group input-box mt-1">
                                    <select class="form-control" name="category" id="sport" required>
                                        @foreach($data['category'] as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> 
                            <div class="form-group mb-2">
                                <label for="team_number">Number of teams <span class="text-danger">*</span></label>
                                <div class="input-group input-box mt-1">
                                    <select class="form-control" name="team" id="team_number" required>
                                        @for ($i = 2; $i <= 20; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label for="team_number">Type of Schedule <span class="text-danger">*</span></label>
                                <div class="input-group input-box mt-1">
                                    <select class="form-control" name="type" id="team_number" required>
                                        <option value="knockout-tournament">Knockout Tournament</option>
                                        <option value="league-round-robin">League Round Robin</option>
                                        <option value="league-cum-knockout">League Cum Knockout</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label for="team_number">Court <span class="text-danger">*</span></label>
                                <div class="input-group input-box mt-1">
                                    <input type="number" name="court" class="form-control" placeholder="Enter number of court">
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label for="team_number">Group <span class="text-danger">*</span></label>
                                <div class="input-group input-box mt-1">
                                    <input type="number" name="group" class="form-control" placeholder="Enter number of group">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary mt-2">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
@endpush
