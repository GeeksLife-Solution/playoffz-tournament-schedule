@extends($theme.'layouts.user')
@section('title')
    {{ 'Pay with '.optional($deposit->gateway)->name ?? '' }}
@endsection
@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card secbg br-4">
                <div class="card-body br-4">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <img
                                src="{{getFile(optional($deposit->gateway)->driver,optional($deposit->gateway)->image)}}"
                                class="card-img-top gateway-img br-4" alt="..">
                        </div>

                        <div class="col-md-9">
                            <h4>@lang('Please Pay') {{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}</h4>
                            <h4 class="mt-15 mb-15">@lang('To Get') {{getAmount($deposit->payable_amount_in_base_currency)}}  {{basicControl()->base_currency}}</h4>
                            <button type="button"
                                    class="btn-custom"
                                    id="btn-confirm">@lang('Pay Now')</button>
                            <form action="{{ route('ipn', [optional($deposit->gateway)->code, $order->trx_id]) }}" method="POST" class="form">
                                @csrf
                                <script
                                    src="//js.paystack.co/v1/inline.js"
                                    data-key="{{ $data->key }}"
                                    data-email="{{ $data->email }}"
                                    data-amount="{{$data->amount}}"
                                    data-currency="{{$data->currency}}"
                                    data-ref="{{ $data->ref }}"
                                    data-custom-button="btn-confirm">
                                </script>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

