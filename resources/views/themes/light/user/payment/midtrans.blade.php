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
                                    class="btn-custom mt-3"
                                    id="pay-button">@lang('Pay Now')
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    @if($data->environment == 'test')
        <script type="text/javascript"
                src="https://app.sandbox.midtrans.com/snap/snap.js"
                data-client-key="{{ $data->client_key }}"></script>
    @else
        <script type="text/javascript"
                src="https://app.midtrans.com/snap/snap.js"
                data-client-key="{{ $data->client_key }}"></script>
    @endif
    <script defer>
        var payButton = document.getElementById('pay-button');
        payButton.addEventListener('click', function () {
            window.snap.pay("{{ $data->token }}", {
                onSuccess: function (result) {
                    let route = '{{ route('ipn', ['midtrans']) }}/';
                    window.location.href = route + result.order_id;
                },
                onPending: function (result) {
                    let route = '{{ route('ipn', ['midtrans']) }}/';
                    window.location.href = route + result.order_id;
                },
                onError: function (result) {
                    window.location.href = '{{ route('failed') }}';
                },
                onClose: function () {
                    window.location.href = '{{ route('failed') }}';
                }
            });
        });
    </script>
@endpush

