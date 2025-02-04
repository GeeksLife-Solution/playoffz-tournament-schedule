@extends('admin.layouts.app')
@section('page_title', __('Option Lists'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Option Lists')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang($gameQuestion->name)</h1>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>@lang('No.')</th>
                        <th>@lang('Options')</th>
                        <th>@lang('Ratio')</th>
                        <th>@lang('Total Prediction')</th>
                        <th>@lang('Status')</th>
                        @if(adminAccessRoute(config('role.manage_result.access.edit')))
                            <th>@lang('Action')</th>
                        @endif
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($gameQuestion->gameOptions as $key => $item)
                        <tr>
                            <td>{{ $loop->index + 1  }}</td>
                            <td>
                                @lang($item->option_name)
                            </td>
                            <td data-label="@lang('Ratio')">@lang($item->ratio)</td>
                            <td data-label="@lang('Total Prediction')">
                                <span class="badge bg-soft-primary text-secondary">{{count($item->betInvestLog)}}</span>
                            </td>
                            <td>
                                @if($item->status ==  0)
                                    <span class="badge bg-soft-danger text-danger">
                                                <span class="legend-indicator bg-danger"></span>@lang('DeActive')
                                            </span>
                                @elseif($item->status ==  1)
                                    <span class="badge bg-soft-warning text-warning">
                                                <span class="legend-indicator bg-warning"></span>@lang('Pending')
                                                </span>
                                @elseif($item->status ==  2)
                                    <span class="badge bg-soft-success text-success">
                                                <span class="legend-indicator bg-success"></span>@lang('Win')
                                                </span>
                                @elseif($item->status ==  -2)
                                    <span class="badge bg-soft-danger text-danger">
                                                <span class="legend-indicator bg-danger"></span>@lang('Lost')
                                                </span>
                                @elseif($item->status ==  3)
                                    <span class="badge bg-soft-danger text-danger">
                                                <span class="legend-indicator bg-danger"></span>@lang('Refunded')
                                                </span>
                                @endif
                            </td>

                            @if(adminAccessRoute(config('role.manage_result.access.edit')))
                                <td data-label="@lang('Action')">
                                    <button type="button"
                                            data-id="{{$item->id}}"
                                            data-route="{{route('admin.makeWinner')}}"
                                            class="btn btn-white btn-sm makeWinner"
                                            {{($gameQuestion->result == 1)?'disabled':''}}
                                            data-bs-target="#makeWinner"
                                            data-bs-toggle="modal">
                                        <i class="fa fa-paper-plane"
                                           aria-hidden="true"></i>
                                    </button>
                                </td>
                            @endif
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
    {{-- Make Winner MODAL --}}
    <div id="makeWinner" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="primary-header-modalLabel">@lang('Make Winner')
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>@lang('Are you want to make winner this?')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <form action="" method="post" class="winnerRoute">
                        @csrf
                        @method('post')
                        <input type="hidden" name="optionId" value="" class="optionId">
                        <button type="submit" class="btn btn-soft-success">@lang('Yes')</button>
                    </form>
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
    <script>
        'use strict';
        $('.makeWinner').on('click', function () {
            var route = $(this).data('route');
            $('.optionId').val($(this).data('id'));
            $('.winnerRoute').attr('action', route)
        });
    </script>
@endpush



