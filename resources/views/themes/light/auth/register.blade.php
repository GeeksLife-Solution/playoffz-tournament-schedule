@extends($theme.'layouts.app')
@section('title',trans('Register'))
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
                                <h2>@lang(@$template['authentication'][0]['description']->register_page_heading)</h2>
                                <a href="{{url('/')}}">@lang('back to home')</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-wrapper d-flex align-items-center h-100">
                        <form action="{{ route('register') }}" method="post">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12">
                                    <h4>@lang('register here')</h4>
                                </div>
                                @if(session()->get('sponsor') != null)
                                    <div class="input-box col-12">
                                        <label>@lang('Sponsor Name')</label>
                                        <input type="text" name="sponsor" class="form-control" id="sponsor"
                                               placeholder="{{trans('Sponsor By') }}"
                                               value="{{session()->get('sponsor')}}" readonly>
                                    </div>
                                @endif
                                <div class="input-box col-6">
                                    <input
                                        type="text"
                                        name="firstname"
                                        value="{{old('firstname')}}"
                                        class="form-control"
                                        placeholder="@lang('First name')"/>
                                    @error('firstname')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <div class="input-box col-6">
                                    <input
                                        type="text"
                                        name="lastname"
                                        value="{{old('lastname')}}"
                                        class="form-control"
                                        placeholder="@lang('Last name')"/>
                                    @error('lastname')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <div class="input-box col-6">
                                    <input
                                        type="text"
                                        name="username"
                                        value="{{old('username')}}"
                                        class="form-control"
                                        placeholder="@lang('Username')"/>
                                    @error('username')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <div class="input-box col-6">
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{old('email')}}"
                                        class="form-control"
                                        placeholder="@lang('Email address')"/>
                                    @error('email')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <div class="input-box col-6">
                                    @php
                                        $country_code = (string) @getIpInfo()['code'] ?: null;
                                        $myCollection = collect(config('country'))->map(function($row) {
                                            return collect($row);
                                        });
                                        $countries = $myCollection->sortBy('code');
                                    @endphp
                                    <select
                                        class="form-select country_code dialCode-change" name="phone_code"
                                        aria-label="Default select example" id="basic-addon1">
                                        <option selected="" disabled>@lang('Select Code')</option>
                                        @foreach(config('country') as $value)
                                            <option value="{{$value['phone_code']}}"
                                                    data-name="{{$value['name']}}"
                                                    data-code="{{$value['code']}}"
                                                {{$country_code == $value['code'] ? 'selected' : ''}}> {{$value['name']}}
                                                ({{$value['phone_code']}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-box col-6">
                                    <input
                                        type="text"
                                        name="phone" value="{{old('phone')}}"
                                        class="form-control dialcode-set"
                                        placeholder="@lang('Phone Number')"/>
                                    @error('phone')
                                    <span class="text-danger mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <input type="hidden" name="country_code" value="{{old('country_code')}}"
                                       class="text-dark">
                                <div class="input-box col-6">
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="@lang('Password')"/>
                                    @error('password')
                                    <span class="text-danger mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="input-box col-6">
                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        class="form-control"
                                        placeholder="@lang('Confirm Password')"/>
                                </div>

                                @if((basicControl()->google_recaptcha == 1) && (basicControl()->google_reCaptcha_status_registration))
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
                                @if(basicControl()->manual_recaptcha &&  basicControl()->reCaptcha_status_registration)
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

                            </div>
                            <button type="submit" class="btn-custom w-100 mt-2">@lang('sign up')</button>
                            <div class="bottom">
                                @lang('Already have an account?')

                                <a href="{{route('login')}}">@lang('Login here')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('extra-js')
    @if((basicControl()->google_recaptcha == 1) && (basicControl()->google_reCaptcha_status_registration))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
@endpush
@push('script')
    <script>
        "use strict";
        $(document).ready(function () {
            setDialCode();
            $(document).on('change', '.dialCode-change', function () {
                setDialCode();
            });

            function setDialCode() {
                let currency = $('.dialCode-change').val();
                $('.dialcode-set').val(currency);
            }
        });

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
