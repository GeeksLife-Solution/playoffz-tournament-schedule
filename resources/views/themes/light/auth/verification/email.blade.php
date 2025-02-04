@extends($theme.'layouts.app')
@section('title',$page_title)
@section('content')
    <style>
        .login-section .text-box {
            background: url({{getFile(@$template['authentication'][0]->content->media->image->driver,@$template['authentication'][0]->content->media->image->path)}});
            background-size: cover;
        }
    </style>
    <section class="login-section">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col-lg-6 p-0">
                    <div class="text-box h-100">
                        <div class="overlay h-100">
                            <div class="text">
                                <h2>@lang('Stay protected, stay connected.')</h2>
                                <a href="{{url('/')}}">@lang('back to home')</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-wrapper d-flex align-items-center h-100">
                        <form action="{{ route('user.mailVerify') }}" method="post">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12">
                                    <h4>@lang('Email Verification Here!')</h4>
                                </div>
                                <div class="input-box col-12">
                                    <input
                                        type="text" name="code"
                                        class="form-control"
                                        placeholder="@lang("Code")"
                                    />
                                    @error('code')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                                @if (Route::has('user.resendCode'))
                                    <div class="col-12">
                                        <div class="links">
                                            <a href="{{route('user.resendCode')}}?type=email"
                                            >@lang('Resend code')?</a>
                                        </div>
                                        @error('resend')
                                        <p class="text-danger mt-1">@lang($message)</p>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            <button type="submit" class="mt-2 btn-custom w-100">@lang('Submit')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

