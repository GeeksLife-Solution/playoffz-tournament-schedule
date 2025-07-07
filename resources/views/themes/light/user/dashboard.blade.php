@extends($theme.'layouts.user')
@section('title',trans('Dashboard'))
@section('content')
    <div class="row">
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$scheduleStats['totalEvents']}}</h2>
                    <p class="info">@lang('Total Events')</p>
                </div>
                <div class="dashboard__card-icon">
                    <i class="fal fa-calendar-alt fa-2x"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$scheduleStats['completedEvents']}}</h2>
                    <p class="info">@lang('Completed Events')</p>
                </div>
                <div class="dashboard__card-icon">
                    <i class="fal fa-check-circle fa-2x"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$scheduleStats['pendingEvents']}}</h2>
                    <p class="info">@lang('Upcoming Events')</p>
                </div>
                <div class="dashboard__card-icon">
                    <i class="fal fa-hourglass-half fa-2x"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$scheduleStats['notStartedEvents']}}</h2>
                    <p class="info">@lang('Past Due Events')</p>
                </div>
                <div class="dashboard__card-icon">
                    <i class="fal fa-exclamation-circle fa-2x"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$scheduleStats['totalMatches']}}</h2>
                    <p class="info">@lang('Total Matches')</p>
                </div>
                <div class="dashboard__card-icon">
                    <i class="fal fa-calendar-star fa-2x"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$scheduleStats['completedMatches']}}</h2>
                    <p class="info">@lang('Completed Matches')</p>
                </div>
                <div class="dashboard__card-icon">
                    <i class="fal fa-check-circle fa-2x"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$scheduleStats['pendingMatches']}}</h2>
                    <p class="info">@lang('Upcoming Matches')</p>
                </div>
                <div class="dashboard__card-icon">
                    <i class="fal fa-hourglass-half fa-2x"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$scheduleStats['notStartedMatches']}}</h2>
                    <p class="info">@lang('Past Due Matches')</p>
                </div>
                <div class="dashboard__card-icon">
                    <i class="fal fa-exclamation-circle fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$scheduleStats['totalWaivers']}}</h2>
                    <p class="info">@lang('Total Waivers')</p>
                </div>
                <div class="dashboard__card-icon">
                    <i class="fal fa-file-signature fa-2x"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$scheduleStats['totalMembers']}}</h2>
                    <p class="info">@lang('Total Members')</p>
                </div>
                <div class="dashboard__card-icon">
                    <i class="fal fa-users fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
@endsection