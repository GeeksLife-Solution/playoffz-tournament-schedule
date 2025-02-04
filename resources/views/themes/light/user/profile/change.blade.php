@extends($theme.'layouts.user')
@section('title',trans('Password Change'))

@section('content')
    <div class="row">
        @include($theme.'user.profile.imageUpload')
        <div class="col-sm-8">
            <div class="card secbg form-block br-4">
                <div class="card-body">
                    @include($theme.'user.profile.profileNav')
                    <!-- Tab panes -->
                    <form method="post" action="{{ route('user.updatePassword') }}">
                        @csrf
                        <label>@lang('Current Password')</label>
                        <div class="form-group input-box mb-3">
                            <input id="password" type="password" class="form-control"
                                   name="current_password" autocomplete="off">
                            @if($errors->has('current_password'))
                                <div
                                    class="error text-danger">@lang($errors->first('current_password')) </div>
                            @endif
                        </div>
                        <label>@lang('New Password')</label>
                        <div class="form-group input-box mb-3">
                            <input id="password" type="password" class="form-control"
                                   name="password" autocomplete="off">
                            @if($errors->has('password'))
                                <div
                                    class="error text-danger">@lang($errors->first('password')) </div>
                            @endif
                        </div>

                        <label>@lang('Confirm Password')</label>
                        <div class="form-group input-box mb-3">
                            <input id="password_confirmation" type="password"
                                   name="password_confirmation" autocomplete="off"
                                   class="form-control">
                            @if($errors->has('password_confirmation'))
                                <div
                                    class="error text-danger">@lang($errors->first('password_confirmation')) </div>
                            @endif
                        </div>

                        <div class="submit-btn-wrapper text-center">
                            <button type="submit"
                                    class="btn-custom w-100">
                                <span>@lang('Update Password')</span></button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('css-lib')
@endpush

@push('extra-js')
@endpush

@push('script')

@endpush
