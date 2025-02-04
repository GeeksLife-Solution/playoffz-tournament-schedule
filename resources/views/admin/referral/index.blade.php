@extends('admin.layouts.app')
@section('page_title',__('Referral Commission'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0);">@lang('Dashboard')</a></li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Referral Commission')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Referral Commission')</h1>
                </div>
            </div>
        </div>
        <div class="row d-flex justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <div class="container">
                            <form action="{{route('admin.referral-commission.action')}}" method="post">
                                @csrf
                                <div class="list-group-item mb-4">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 ms-3">
                                            <div class="row align-items-center">
                                                <div class="col-sm mb-2 mb-sm-0">
                                                    <h5 class="mb-0">@lang('Deposit Bonus')</h5>
                                                    <p class="fs-5 text-body mb-0">@lang('To activate the deposit Commission, please switch on this button.')</p>
                                                </div>
                                                <div class="col-sm-auto d-flex align-items-center">
                                                    <div class="form-check form-switch form-switch-google">
                                                        <input type="hidden" name="deposit_commission" value="0">
                                                        <input class="form-check-input" name="deposit_commission" type="checkbox" id="deposit_commission" value="1" @checked(basicControl()->deposit_commission)>
                                                        <label class="form-check-label" for="deposit_commission"></label>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item mb-4">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 ms-3">
                                            <div class="row align-items-center">
                                                <div class="col-sm mb-2 mb-sm-0">
                                                    <h5 class="mb-0">@lang('Bet Commission')</h5>
                                                    <p class="fs-5 text-body mb-0">@lang('To activate the Bet Commission, please switch on this button. ')</p>
                                                </div>
                                                <div class="col-sm-auto d-flex align-items-center">
                                                    <div class="form-check form-switch form-switch-google">
                                                        <input type="hidden" name="bet_commission" value="0">
                                                        <input class="form-check-input" name="bet_commission" type="checkbox" id="bet_commission" value="1" @checked(basicControl()->bet_commission)>
                                                        <label class="form-check-label" for="bet_commission"></label>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item mb-4">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 ms-3">
                                            <div class="row align-items-center">
                                                <div class="col-sm mb-2 mb-sm-0">
                                                    <h5 class="mb-0">@lang('Bet Win Commission')</h5>
                                                    <p class="fs-5 text-body mb-0">@lang('To activate the Bet Win Commission, please switch on this button.')</p>
                                                </div>
                                                <div class="col-sm-auto d-flex align-items-center">
                                                    <div class="form-check form-switch form-switch-google">
                                                        <input type="hidden" name="bet_win_commission" value="0">
                                                        <input class="form-check-input" name="bet_win_commission" type="checkbox" id="bet_win_commission" value="1" @checked(basicControl()->bet_win_commission)>
                                                        <label class="form-check-label" for="bet_win_commission"></label>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary mt-2 ">@lang('Save Changes')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h4 class="card-header-title">
                            @lang('Deposit Bonus')
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Table -->
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col" class="text-center">@lang('Level')</th>
                                <th scope="col" class="text-center">@lang('Bonus')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($referrals->where('commission_type','deposit') as $item)
                                <tr>
                                    <th scope="row" class="text-center">@lang('LEVEL')# {{ $item->level }}</th>
                                    <td class="text-center">{{ $item->percent.'%'}}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">@lang('No Data Found')</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <!-- End Table -->
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        <h4 class="card-header-title">
                            @lang('Bet Invest Bonus')
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Table -->
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col" class="text-center">@lang('Level')</th>
                                <th scope="col" class="text-center">@lang('Bonus')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($referrals->where('commission_type','bet_invest') as $item)
                                <tr>
                                    <th scope="row" class="text-center">@lang('LEVEL')# {{ $item->level }}</th>
                                    <td class="text-center">{{ $item->percent.'%'}}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">@lang('No Data Found')</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <!-- End Table -->
                    </div>
                </div>
                <div class="card mt-5">
                    <div class="card-header">
                        <h4 class="card-header-title">
                            @lang('Bet Win Bonus')
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Table -->
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col" class="text-center">@lang('Level')</th>
                                <th scope="col" class="text-center">@lang('Bonus')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($referrals->where('commission_type','bet_win') as $item)
                                <tr>
                                    <th scope="row" class="text-center">@lang('LEVEL')# {{ $item->level }}</th>
                                    <td class="text-center">{{ $item->percent.'%'}}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">@lang('No Data Found')</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <!-- End Table -->
                    </div>
                </div>

            </div>
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.referral-commission.store')}}" method="post">
                            @method('post')
                            @csrf
                            <div class="row">
                                <div class="col-md-5">
                                    <!-- Select -->
                                    <label class="mb-1">@lang('Select commission type')</label>
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" autocomplete="off" name="commission_type" id="commissionType"
                                                data-hs-tom-select-options='{
                                                          "placeholder": "Select Type",
                                                          "hideSearch": true
                                                        }'>
                                            <option value="" >@lang('Select Type')</option>
                                            <option value="deposit">@lang('Deposit Bonus')</option>
                                            <option value="bet_invest">@lang('Bet Invest Bonus')</option>
                                            <option value="bet_win">@lang('Bet Win Bonus')</option>
                                        </select>
                                    </div>
                                    <!-- End Select -->
                                </div>
                                <div class="col-md-5">
                                    <div class="mb-3">
                                        <label class="mb-1">@lang('Number Of Level')</label>
                                        <input type="text" id="NumberOfLevel" class="form-control" placeholder="e.g : 10">
                                    </div>
                                </div>
                                <div class="col-md-2 mt-4">
                                    <button type="button" class="btn btn-primary generateBtn">@lang('Generate')</button>
                                </div>



                                <div class="elementContainer  mt-5" id="elementContainer">

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush


@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
@endpush

@push('script')
    <script>
        (function() {
            HSCore.components.HSTomSelect.init('.js-select')
        })();

        $(document).on('click','.generateBtn',function (){
            let numberOfLevel = Number($('#NumberOfLevel').val());
            let type = $('#commissionType').val();
            if(!type){
                Notiflix.Notify.failure('Please select commission type');
                return;
            }
            if(!numberOfLevel){
                Notiflix.Notify.failure('Please enter Number of level');
                return ;
            }

            let markup  = '';
            for(let i = 0; i <  parseInt(numberOfLevel) ; i++){
                let currencySymbol = '{{basicControl()->currency_symbol}}';
                markup += `<div class="row">
                                    <div class="col-md-5">
                                        <div class="input-group mb-3 ">
                                            <span class="input-group-text" id="basic-addon1">LEVEL</span>
                                            <input type="text" class="form-control" name="level[]" value="${i+1}" aria-label="Username" aria-describedby="basic-addon1" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control" name="percent[]" placeholder="Level Bonus" aria-label="Recipient's username" aria-describedby="basic-addon2" step="0.01">
                                           <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-white deleteBtn"><i class="bi-trash"></i></button>
                                    </div>
                                </div>`;
            }

            markup += `<div class="submit-btn">
                                        <button class="btn btn-primary" type="submit">Submit</button>
                                    </div>`
            $('#elementContainer').html(markup) ;

        })

        $(document).on('click','.deleteBtn',function (){
            $(this).closest('.row').remove();
        })

    </script>

    @if ($errors->any())
        @php
            $collection = collect($errors->all());
            $errors = $collection->unique();
        @endphp
        <script>
            "use strict";
            @foreach ($errors as $error)
            Notiflix.Notify.failure('{{$error}}');
            @endforeach
        </script>
    @endif
@endpush





