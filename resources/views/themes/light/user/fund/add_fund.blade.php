@extends($theme.'layouts.user')
@section('title','Add Fund')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- checkout section -->
            <div class="card secbg form-block p-0 br-4">
                <div class="card-body">
                    <form action="{{route('payment.request')}}" method="POST" id="paymentForm">
                        @csrf
                        <div class="row g-4 g-lg-5">
                            <div class="col-md-6 col-lg-7">
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <div class="payment-box mb-4">
                                            <h5 class="payment-option-title">@lang('Preferred Funding Method')</h5>
                                            <div class="payment-option-wrapper">
                                                <div class="payment-section">
                                                    <ul class="payment-container-list">
                                                        @if(!empty($gateways))
                                                            @foreach($gateways as $key => $gateway)
                                                                <li class="item">
                                                                    <input
                                                                        class="form-check-input selectPaymentMethod"
                                                                        value="{{ $gateway->id }}" type="radio"
                                                                        name="gateway_id"
                                                                        id="{{ $gateway->name }}">
                                                                    <label class="form-check-label"
                                                                           for="{{ $gateway->name }}">
                                                                        <div class="image-area">
                                                                            <img
                                                                                src="{{ getFile($gateway->driver,$gateway->image ) }}"
                                                                                alt="">
                                                                        </div>
                                                                        <div class="content-area">
                                                                            <h5>{{$gateway->name}}</h5>
                                                                            <span>{{$gateway->description}}</span>
                                                                        </div>
                                                                    </label>

                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-5">
                                <div class="side-bar mt-5">
                                    <div class="side-box mt-2">
                                        <div class="col-md-12 input-box mb-3">
                                            <select class="js-example-basic-single form-select"
                                                    name="supported_currency"
                                                    id="supported_currency">
                                                <option value="">@lang('Select Currency')</option>
                                            </select>
                                        </div>

                                        <div class="col-md-12 input-box">
                                            <div class="input-group">
                                                <input
                                                    class="form-control @error('amount') is-invalid @enderror"
                                                    name="amount"
                                                    type="number" step="any" id="amount"
                                                    placeholder="@lang('Enter Amount')" autocomplete="off"/>
                                                <div class="invalid-feedback">
                                                    @error('amount') @lang($message) @enderror
                                                </div>
                                                <div class="valid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="payoutSummary">
                                        <div class="row d-flex text-center justify-content-center">
                                            <div class="col-md-12">
                                                @if(session()->get('dark-mode') == 'true')
                                                    <img src="{{ asset('assets/admin/img/oc-error-light.svg') }}"
                                                         id="no-data-image" class="no-data-image" alt="" srcset="">
                                                @else
                                                    <img src="{{ asset('assets/admin/img/oc-error.svg') }}"
                                                         id="no-data-image" class="no-data-image" alt="" srcset="">
                                                @endif
                                                <p>@lang('Waiting for payment preview')</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';

        $(document).ready(function () {
            let amountField = $('#amount');
            let amountStatus = false;
            let selectedPaymentMethod = "";

            function clearMessage(fieldId) {
                $(fieldId).removeClass('is-valid')
                $(fieldId).removeClass('is-invalid')
                $(fieldId).closest('div').find(".invalid-feedback").html('');
                $(fieldId).closest('div').find(".is-valid").html('');
            }

            $(document).on('click', '.selectPaymentMethod', function () {
                let id = this.id;
                selectedPaymentMethod = $(this).val();
                supportCurrency(selectedPaymentMethod);
            });

            function supportCurrency(selectedPaymentMethod) {
                if (!selectedPaymentMethod) {
                    console.error('Selected Gateway is undefined or null.');
                    return;
                }

                $('#supported_currency').empty();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "{{ route('supported.currency') }}",
                    data: {gateway: selectedPaymentMethod},
                    type: "GET",
                    success: function (data) {

                        if (data === "") {
                            let markup = `<option value="USD">USD</option>`;
                            $('#supported_currency').append(markup);
                        }

                        let markup = '<option value="">Selected Currency</option>';
                        $('#supported_currency').append(markup);
                        let res = data.data;
                        $(res).each(function (index, value) {
                            let markup = `<option value="${value}">${value}</option>`;
                            $('#supported_currency').append(markup);
                        });
                    },
                    error: function (error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }


            $(document).on('change, input', "#amount, #supported_currency, .selectPaymentMethod", function (e) {

                let amount = amountField.val();
                let selectedCurrency = $('#supported_currency').val();
                let currency_type = 1;

                if (!isNaN(amount) && amount > 0) {

                    let fraction = amount.split('.')[1];
                    let limit = currency_type == 0 ? 8 : 2;


                    if (fraction && fraction.length > limit) {
                        amount = (Math.floor(amount * Math.pow(10, limit)) / Math.pow(10, limit)).toFixed(limit);
                        amountField.val(amount);
                    }

                    checkAmount(amount, selectedCurrency, selectedPaymentMethod)


                } else {
                    clearMessage(amountField)
                    $('#payoutSummary').html(`<div class="row d-flex text-center justify-content-center">
                                                    <div class="col-md-12">
                                                        <img src="{{ asset('assets/admin/img/oc-error.svg') }}" id="no-data-image" class="no-data-image" alt="" srcset="">
                                                        <p>@lang('Waiting for payment preview')</p>
                                                    </div>
                                                </div>`)
                }
            });


            function checkAmount(amount, selectedCurrency, selectedPaymentMethod) {

                $.ajax({
                    method: "GET",
                    url: "{{ route('deposit.checkAmount') }}",
                    dataType: "json",
                    data: {
                        'amount': amount,
                        'selected_currency': selectedCurrency,
                        'select_gateway': selectedPaymentMethod,
                    }
                }).done(function (response) {
                    console.log(response)
                    let amountField = $('#amount');
                    if (response.status) {
                        clearMessage(amountField);
                        $(amountField).addClass('is-valid');
                        $(amountField).closest('div').find(".valid-feedback").html(response.message);
                        amountStatus = true;
                        let base_currency = "{{basicControl()->base_currency}}"
                        showCharge(response, base_currency);
                    } else {
                        amountStatus = false;
                        // submitButton();
                        $('#payoutSummary').html(`<div class="row d-flex text-center justify-content-center">
                                                    <div class="col-md-12">
                                                        <img src="{{ asset('assets/admin/img/oc-error.svg') }}" id="no-data-image" class="no-data-image" alt="" srcset="">
                                                        <p>@lang('Waiting for payout preview')</p>
                                                    </div>
                                                </div>`);
                        clearMessage(amountField);
                        $(amountField).addClass('is-invalid');
                        $(amountField).closest('div').find(".invalid-feedback").html(response.message);
                    }


                });
            }


            function showCharge(response, currency) {

                let txnDetails = `<div class="side-box mt-2">
                    <h5>@lang('Payment Summary')</h5>
                    <div class="showCharge">
                        <ul class="list-group">
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Amount In') }} ${response.currency} </span>
							<span class="text-success"> ${response.amount} ${response.currency}</span>
						</li>

						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Charge') }}</span>
							<span class="text-danger">  ${response.charge} ${response.currency}</span>
						</li>


						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Payment Amount') }}</span>
							<span class=""> ${response.payable_amount} ${response.currency}</span>
						</li>


						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('In Base Currency') }}</span>
							<span class=""> ${response.payable_amount_baseCurrency} ${currency}</span>
						</li>
					</ul>
                    </div>
                </div>
                <button type="submit" class="btn-custom">@lang('Continue') <span></span></button>`;
                $('#payoutSummary').html(txnDetails)
            }
        });
    </script>
@endpush
