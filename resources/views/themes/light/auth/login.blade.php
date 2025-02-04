@extends($theme.'layouts.app')
@section('title',trans('Login'))
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
                                <h2>@lang(@$template['authentication'][0]['description']->login_page_heading)</h2>
                                <a href="{{url('/')}}">@lang('back to home')</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-wrapper d-flex align-items-center h-100">
                        <form action="{{ route('login') }}" method="post">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12">
                                    <h4>@lang('Login here')</h4>
                                </div>
                                <div class="input-box col-12">
                                    <input
                                        type="text" name="username" value="{{old('username')}}"
                                        class="form-control"
                                        placeholder="@lang("Email or Username")"
                                    />
                                    @error('username')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="input-box col-12">
                                    <input
                                        type="password" name="password" value="{{old('password')}}"
                                        class="form-control"
                                        placeholder="@lang('Password')"
                                    />
                                    @error('password')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>

                                @if((basicControl()->google_recaptcha == 1) && (basicControl()->google_reCaptcha_status_login))
                                    <div class="row mt-4">
                                        <div
                                            class="g-recaptcha @error('g-recaptcha-response') is-invalid @enderror"
                                            data-sitekey="{{ env('GOOGLE_RECAPTCHA_SITE_KEY') }}"></div>
                                        @error('g-recaptcha-response')
                                        <span class="invalid-feedback d-block text-danger" role="alert">
                                                      <strong>{{ $message }}</strong>
                                                    </span>
                                        @enderror
                                    </div>
                                @endif
                                @if(basicControl()->manual_recaptcha &&  basicControl()->reCaptcha_status_login)
                                    <div class="input-box col-12">
                                        <input type="text" tabindex="2"
                                               class="form-control @error('captcha') is-invalid @enderror"
                                               name="captcha" id="captcha" autocomplete="off"
                                               placeholder="@lang('Enter captcha code')">

                                        @error('captcha')
                                        <div class="text-danger">@lang($message)</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <div
                                            class="input-group input-group-merge d-flex justify-content-between"
                                            data-hs-validation-validate-class>
                                            <img src="{{route('captcha').'?rand='. rand()}}"
                                                 id='captcha_image2'>
                                            <a class="input-group-append input-group-text"
                                               href='javascript: refreshCaptcha2();'>
                                                <i class="fal fa-sync"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-12">
                                    <div class="links">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="remember"
                                                value=""
                                                id="flexCheckDefault" {{ old('remember') ? 'checked' : '' }}
                                            />
                                            <label
                                                class="form-check-label"
                                                for="flexCheckDefault"
                                            >
                                                @lang('Remember me')
                                            </label>
                                        </div>
                                        <a href="{{ route('password.request') }}"
                                        >@lang('Forgot password?')</a>
                                    </div>
                                </div>
                            </div>

                            <button class="btn-custom w-100" type="submit">@lang('sign in')</button>
                            <div class="bottom">
                                @lang("Don't have an account?")

                                <a href="{{ route('register') }}">@lang('Create an account')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('extra-js')
    @if((basicControl()->google_recaptcha == 1) && (basicControl()->google_reCaptcha_status_login))
        <script async src="https://www.google.com/recaptcha/api.js"></script>
    @endif
@endpush
@push('script')
    <script>
        'use strict';

        function refreshCaptcha() {
            let img = document.images['captcha_image'];
            img.src = img.src.substring(
                0, img.src.lastIndexOf("?")
            ) + "?rand=" + Math.random() * 1000;
        }

        function refreshCaptcha2() {
            let img = document.images['captcha_image2'];
            img.src = img.src.substring(
                0, img.src.lastIndexOf("?")
            ) + "?rand=" + Math.random() * 1000;
        }
    </script>
@endpush
