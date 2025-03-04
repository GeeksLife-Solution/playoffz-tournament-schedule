@extends($theme . 'layouts.user')
@section('title', trans($title))
@section('content')

    <div class="row justify-content-between ">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="title text-start m-0">@lang('Game Schedules')</h5>
                    <a href="{{route('user.schedule.create')}}" class="btn btn-primary">Create Schedule</a>
                </div>
                <div class="card-body">
                    <div class="row m-0 mt-4">
                        @if($data['schedule']->count() > 0)
                            @foreach($data['schedule'] as $schedule)
                                <div class="col-md-4">
                                    @php
                                        $route = route('user.schedule.edit');
                                        $params = ["schedule_id" => $schedule->id];
                                        $encryptedUrl = encryptUrl($route, $params);
                                    @endphp
                                    <a href="{{$encryptedUrl}}" class="card shadow-sm mb-4">
                                        <div class="card-header text-center bg-primary text-white">
                                            <h5 class="m-0">@lang($schedule->name)</h5>
                                            <p class="text-white mb-0">{{dateTime($schedule->created_at, 'd M Y H:i')}}</p>
                                        </div>
                                        <div class="card-body text-center">
                                            <div class="position-relative">
                                                <img src="{{$schedule->image}}" 
                                                alt="@lang($schedule->name)" 
                                                class="img-fluid rounded mb-3" 
                                                style="height: 200px; object-fit: cover;">
                                                <p class="position-absolute top-0 end-0">
                                                    <span class="badge {{ $schedule->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                                        @lang($schedule->status == 1 ? 'Publish' : 'Unpublish')
                                                    </span>
                                                </p>
                                            </div>                
                                            <div class="schedule-details">
                                                <div class="d-flex justify-content-around">
                                                    <p class="mb-0 text-white"><strong>@lang('Category'):</strong> @lang($schedule->gameCategory->name ?? 'N/A')</p>
                                                    <p class="mb-0 text-white"><strong>@lang('Number of Teams'):</strong> {{ $schedule->teams }}</p>
                                                </div>
                                                <p class="mt-1mb-0 text-capitalize text-white"><strong>@lang('Type'):</strong> {{ str_replace('-', ' ', $schedule->type) }}</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <div class="col-md-12 text-center">
                                <div class="alert alert-info">
                                    @lang('No schedules found.')
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
@endpush