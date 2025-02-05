@extends('admin.layouts.app')
@section('page_title','Schedule List')
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Schedule List')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Schedule List')</h1>
                </div>
                <div class="col-sm-auto">
                    @if(adminAccessRoute(config('role.manage_game.access.add')))
                        <a class="btn btn-primary" href="{{route('admin.createSchedule')}}">
                            <i class="fas fa-gamepad me-1"></i> @lang('Add New')
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        Schedule list here
                    </div>
                </div>
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
