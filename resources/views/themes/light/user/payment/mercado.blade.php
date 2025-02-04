@extends($theme.'layouts.user')

@section('title')
    {{ 'Pay with '.optional($deposit->gateway)->name ?? '' }}
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card secbg">
                <div class="card-body ">
                    <div class="row justify-content-center">
                        <div class="col-md-3">
                            <img
                                src="{{getFile(optional($deposit->gateway)->driver,optional($deposit->gateway)->image)}}"
                                class="card-img-top gateway-img" alt="..">
                        </div>

                        <div class="col-md-9">
                            <h4>@lang('Please Pay') {{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}</h4>
                            <h4 class="mt-15 mb-15">@lang('To Get') {{getAmount($deposit->payable_amount_in_base_currency)}}  {{basicControl()->base_currency}}</h4>

                            <form action="{{ route('ipn', [optional($deposit->gateway)->code ?? 'mercadopago', $order->trx_id]) }}"
                                method="POST">
                                <script
                                    src="https://www.mercadopago.com.co/integrations/v1/web-payment-checkout.js"
                                    data-preference-id="{{ $data->preference }}">
                                </script>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
