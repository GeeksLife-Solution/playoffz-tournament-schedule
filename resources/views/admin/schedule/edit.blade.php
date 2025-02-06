@extends('admin.layouts.app')
@section('page_title','Manage Schedule')
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Manage Schedule')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Manage Schedule')</h1>
                </div>
                <div class="col-sm-auto">
                    <a class="btn btn-primary btn-sm" href="{{route('admin.listSchedule')}}">
                        <i class="fas fa-plus-circle me-1"></i> @lang('Schedule List')
                    </a>
                </div>
            </div>
        </div>

        <style>
            .schedule-min-title{
                border-bottom: 1px solid #cacaca;
                margin-bottom: 15px;
                text-transform: uppercase;
                padding-bottom: 2px;
                color: #cacaca;
            }
        </style>

        <div class="row">
            <div class="col-lg-12">
                <h1 class="text-center mb-3">{{$schedule->num_teams}} Player {{$schedule->sport}} Schedule</h1>

                <h4 class="schedule-min-title">Standings</h4>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm table-striped text-center">
                        <thead class="thead-light">
                        <tr>
                            <th>Participant</th>
                            <th>M</th>
                            <th>W</th>
                            <th>L</th>
                            <th>Draws</th>
                            <th>Pts</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($schedule->standings as $standing)
                            <tr>
                                <td><a href="" class="text-danger">{{$standing->participant->participant_name}}</a></td>
                                <td>{{$standing->matches_played}}</td>
                                <td>{{$standing->wins}}</td>
                                <td>{{$standing->losses}}</td>
                                <td>{{$standing->draws}}</td>
                                <td>{{$standing->points}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <h4 class="schedule-min-title d-flex justify-content-between"><span  class="align-self-center">Participants</span> <button class="mb-2 btn btn-xs btn-info">Add New</button></h4>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm table-striped text-center">
                        <thead class="thead-light">
                        <tr>
                            <th>Participant ID</th>
                            <th>Schedule ID</th>
                            <th>Participant Name</th>
                            <th>Team Members</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($schedule->participants as $participant)
                            <tr>
                                <td>{{$participant->id}}</td>
                                <td>{{$participant->schedule_id}}</td>
                                <td>{{$participant->participant_name}}</td>
                                <td>{{$participant->is_team ? 'Yes' : 'No'}}</td>
                                <td>
                                    <a href="" class="text-danger me-2">Edit</a>
                                    <a href=""  class="text-danger">Delete</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <h4 class="schedule-min-title">Schedule</h4>
                @foreach($schedule->matchSchedules as $matchSchedule)
                <div class="card p-3 mb-3">
                    <a class="text-danger mb-1" href="">{{ $matchSchedule->participant1->participant_name }}</a>
                    <a class="text-danger" href="">{{ $matchSchedule->participant2->participant_name }}</a>
                    <div class="d-flex mt-3">
                        <span class="me-3">Match {{$loop->iteration}}</span>
                        <a class="me-2 text-danger" href="">View</a>
                        <a href="" class="text-danger">Edit</a>
                    </div>
                </div>
                @endforeach

            </div>
        </div>
    </div>
@endsection




@push('css-lib')

@endpush


@push('js-lib')

@endpush

@push('script')

@endpush
