@extends('admin.layouts.app')
@section('page_title', __('Better List'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">
                                    @lang('Dashboard')
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Better List')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">{{@$matchName}} <small>({{$question->name}})</small></h1>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>@lang('No.')</th>
                        <th>@lang('User')</th>
                        <th>@lang('Question')</th>
                        <th>@lang('Option')</th>
                        <th>@lang('Result')</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($betInvestLogs as $key => $item)
                        <tr>
                            <td data-label="@lang('SL No.')">{{++$key}}</td>
                            <td data-label="@lang('User')">
                                <a class="d-flex align-items-center me-2"
                                   href="{{route('admin.user.edit',$item->user_id)}}">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm avatar-circle">
                                            <img class="avatar-img" src="{{getFile(optional($item->user)->image_driver,optional($item->user)->image) }}" alt="Image Description">
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="text-hover-primary mb-0">@lang(optional($item->user)->username)</h5>
                                        <span class="fs-6 text-body">{{optional($item->user)->email}}</span>
                                    </div>
                                </a>
                            </td>
                            <td data-label="@lang('Question')">@lang(optional($item->gameQuestion)->name)</td>
                            <td data-label="@lang('Option')">@lang(optional($item->gameOption)->option_name)</td>
                            <td data-label="@lang('Result')">
                                @if($item->gameQuestion->winOption)
                                    <span
                                        class="badge bg-success">{{@$item->gameQuestion->winOption->option_name}}</span>
                                @else
                                    <span class="badge bg-warning">@lang('N/A')</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="odd">
                            <td valign="top" colspan="8" class="dataTables_empty">
                                <div class="text-center p-4">
                                    <img class="mb-3 dataTables-image"
                                         src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description"
                                         data-hs-theme-appearance="default">
                                    <img class="mb-3 dataTables-image"
                                         src="{{ asset('assets/admin/img/oc-error-light.svg') }}"
                                         alt="Image Description" data-hs-theme-appearance="dark">
                                    <p class="mb-0">@lang("No data to show")</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('css-lib')
@endpush

@push('js-lib')
@endpush

@push('script')
    <script>
        'use strict';

    </script>
@endpush



