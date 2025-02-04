@extends($theme.'layouts.user')
@section('title',trans('Profile Settings'))

@section('content')
    <div class="row">
        @include($theme.'user.profile.imageUpload')

        <div class="col-sm-8">
            <div class="card secbg form-block br-4">
                <div class="card-body">
                    @include($theme.'user.profile.profileNav')
                    <!-- Tab panes -->
                    <form action="{{ route('user.updateInformation')}}" method="post">
                        @method('post')
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label>@lang('First Name')</label>
                                <div class="form-group input-box mb-3">
                                    <input class="form-control" type="text" name="firstname"
                                           value="{{old('firstname')?: $user->firstname }}">
                                    @if($errors->has('firstname'))
                                        <div
                                            class="error text-danger">@lang($errors->first('firstname')) </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label>@lang('Last Name')</label>
                                <div class="form-group input-box mb-3">
                                    <input class="form-control" type="text" name="lastname"
                                           value="{{old('lastname')?: $user->lastname }}">
                                    @if($errors->has('lastname'))
                                        <div
                                            class="error text-danger">@lang($errors->first('lastname')) </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label>@lang('Username')</label>
                                <div class="form-group input-box mb-3">
                                    <input class="form-control" type="text" name="username"
                                           value="{{old('username')?: $user->username }}">
                                    @if($errors->has('username'))
                                        <div
                                            class="error text-danger">@lang($errors->first('username')) </div>
                                    @endif
                                </div>
                            </div>


                            <div class="col-md-6">
                                <label>@lang('Email Address')</label>
                                <div class="form-group input-box mb-3">
                                    <input class="form-control" type="email"
                                           value="{{ $user->email }}" readonly>
                                    @if($errors->has('email'))
                                        <div
                                            class="error text-danger">@lang($errors->first('email')) </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label>@lang('Phone Number')</label>
                                <div class="form-group input-box mb-3">
                                    <input class="form-control" type="text" readonly
                                           value="{{$user->phone}}">

                                    @if($errors->has('phone'))
                                        <div
                                            class="error text-danger">@lang($errors->first('phone')) </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label>@lang('Preferred language')</label>
                                <div class="form-group input-box mb-3">
                                    <select name="language_id" id="language_id" class="form-select">
                                        <option value="" disabled>@lang('Select Language')</option>
                                        @foreach($languages as $la)
                                            <option value="{{$la->id}}"

                                                {{ old('language_id', $user->language_id) == $la->id ? 'selected' : '' }}>@lang($la->name)</option>
                                        @endforeach
                                    </select>

                                    @if($errors->has('language_id'))
                                        <div
                                            class="error text-danger">@lang($errors->first('language_id')) </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <label>@lang('Address')</label>
                        <div class="form-group input-box mb-3">
                                    <textarea class="form-control" name="address"
                                              rows="5">@lang($user->address_one)</textarea>

                            @if($errors->has('address'))
                                <div
                                    class="error text-danger">@lang($errors->first('address')) </div>
                            @endif
                        </div>

                        <div class="submit-btn-wrapper text-center text-md-left">
                            <button type="submit"
                                    class="btn-custom w-100">
                                <span>@lang('Update User')</span></button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('css-lib')
    <link rel="stylesheet" href="{{asset($themeTrue.'css/bootstrap-fileinput.css')}}">
@endpush

@push('extra-js')
    <script src="{{asset($themeTrue.'js/bootstrap-fileinput.js')}}"></script>
@endpush

@push('script')
    <script>
        "use strict";
        $(document).on('click', '#image-label', function () {
            $('#image').trigger('click');
        });
        $(document).on('change', '#image', function () {
            var _this = $(this);
            var newimage = new FileReader();
            newimage.readAsDataURL(this.files[0]);
            newimage.onload = function (e) {
                $('#image_preview_container').attr('src', e.target.result);
            }
        });

        $(document).on('change', "#identity_type", function () {
            let value = $(this).find('option:selected').val();
            window.location.href = "{{route('user.profile')}}/?identity_type=" + value
        });
    </script>
@endpush
