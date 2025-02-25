@extends($theme.'layouts.user')
@section('title',trans('Schedule List'))
@section('content')
    <div class="section">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Schedule List')</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-sm-auto">
                    @if(adminAccessRoute(config('role.manage_game.access.add')))
                        <a class="btn btn-primary btn-sm mb-3" href="{{route('user.createSchedule')}}">
                            <i class="fas fa-plus-circle me-1"></i> @lang('Add New')
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-striped">
                                <thead class="thead-light">
                                <tr>
                                    <th>Sport</th>
                                    <th>Type Of Schedule</th>
                                    <th>Number Of Team</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($schedules as $schedule)
                                <tr>
                                    <td>{{$schedule->sport}}</td>
                                    <td>{{$schedule->type_of_schedule}}</td>
                                    <td>{{$schedule->num_teams}}</td>
                                    <td>{{$schedule->start_date}}</td>
                                    <td>{{$schedule->end_date}}</td>
                                    <td>
                                        <a href="{{route('user.editSchedule', $schedule->id)}}" class="btn btn-info btn-xs">Manage Schedule</a>
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
@endsection

@push('script')

@endpush
