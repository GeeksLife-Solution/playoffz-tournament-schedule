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
                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12">
                                    <h4>@lang('Reset Passwords')</h4>
                                </div>
                                <div class="input-box col-12">
                                    <input
                                        type="email" name="email"
                                        class="form-control"
                                        placeholder="@lang("Email Address")"
                                    />
                                    @error('email')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>

                            <button class="btn-custom w-100 mt-2" type="submit">@lang('Send Password Reset Link')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
