@extends($theme.'layouts.user')
@section('title')
    {{ 'Pay with '.optional($deposit->gateway)->name ?? '' }}
@endsection
@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card secbg br-4">
                <div class="card-body ">
                    <div class="row ">
                        <div class="col-md-12">
                            <h3 class="title text-center">{{trans('Please follow the instruction below')}}</h3>
                            <p class="text-center mt-2 ">{{trans('You have requested to deposit')}} <b
                                    class="text--base">{{getAmount($deposit->payable_amount_in_base_currency)}}
                                    {{basicControl()->base_currency}}</b> , {{trans('Please pay')}}
                                <b class="text--base">{{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}</b> {{trans('for successful payment')}}
                            </p>

                            <p class=" mt-2 ">
                                <?php echo optional($deposit->gateway)->note; ?>
                            </p>

                            <form action="{{route('addFund.fromSubmit',$deposit->trx_id)}}" method="post"
                                  enctype="multipart/form-data"
                                  class="form-row  preview-form">
                                @csrf
                                @if(optional($deposit->gateway)->parameters)
                                    @foreach($deposit->gateway->parameters as $k => $v)
                                        @if($v->type == "text")
                                            <div class="col-md-12 mt-2">
                                                <label>{{trans($v->field_label)}} @if($v->validation == 'required')
                                                        <span class="text--danger">*</span>
                                                    @endif </label>
                                                <div class="form-group input-box">
                                                    <input type="{{$v->type}}" name="{{$k}}" class="form-control"
                                                           @if($v->validation == "required") required @endif>
                                                    @if ($errors->has($k))
                                                        <span
                                                            class="text--danger">{{ trans($errors->first($k)) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif($v->type == "textarea")
                                            <div class="col-md-12 mt-2">
                                                <label>{{trans($v->field_label)}} @if($v->validation == 'required')
                                                        <span class="text--danger">*</span>
                                                    @endif </label>
                                                <div class="form-group input-box">
                                                    <textarea name="{{$k}}" class="form-control" rows="3"
                                                              @if($v->validation == "required") required @endif></textarea>
                                                    @if ($errors->has($k))
                                                        <span
                                                            class="text--danger">{{ trans($errors->first($k)) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif($v->type == "file")
                                            <div class="col-md-12 mt-2">
                                                <label>{{trans($v->field_label)}} @if($v->validation == 'required')
                                                        <span class="text--danger">*</span>
                                                    @endif </label>

                                                <div class="form-group">
                                                    <div class="fileinput fileinput-new " data-provides="fileinput">
                                                        <div class="fileinput-new thumbnail withdraw-thumbnail"
                                                             data-trigger="fileinput">
                                                            <img class="w-25"
                                                                 src="{{ getFile('dummy','dummy') }}"
                                                                 alt="...">
                                                        </div>
                                                        <div
                                                            class="fileinput-preview fileinput-exists thumbnail wh-200-150 "></div>

                                                        <div class="img-input-div">
                                                                <span class="btn btn-success btn-file">
                                                                    <span
                                                                        class="fileinput-new "> @lang('Select') {{$v->field_label}}</span>
                                                                    <span
                                                                        class="fileinput-exists"> @lang('Change')</span>
                                                                    <input type="file" name="{{$k}}" accept="image/*"
                                                                           @if($v->validation == "required") required @endif>
                                                                </span>
                                                            <a href="#" class="btn btn-danger fileinput-exists"
                                                               data-dismiss="fileinput"> @lang('Remove')</a>
                                                        </div>

                                                    </div>
                                                    @if ($errors->has($k))
                                                        <br>
                                                        <span
                                                            class="text--danger">{{ __($errors->first($k)) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif

                                <div class="col-md-12 ">
                                    <div class=" form-group">
                                        <button type="submit" class="btn-custom w-100 mt-3">
                                            <span>@lang('Confirm Now')</span>
                                        </button>
                                    </div>
                                </div>
                            </form>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('css-lib')
        <link rel="stylesheet" href="{{asset($themeTrue.'css/bootstrap-fileinput.css')}}">
    @endpush

    @push('extra-js')
        <script src="{{asset($themeTrue.'js/bootstrap-fileinput.js')}}"></script>
    @endpush
@endsection
