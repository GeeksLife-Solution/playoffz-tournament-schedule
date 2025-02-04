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
                    <form action="{{route('user.kyc.verification.submit',$kyc->id)}}" method="post"
                          enctype="multipart/form-data">
                        @method('post')
                        @csrf

                        @if($kyc->kycPosition() == 'verified')
                            <div class="card-header border-0 text-start text-md-center">
                                <h5 class="card-title">@lang('KYC Information')</h5>
                                <p class="text-success">@lang('Your kyc is verified')</p>
                            </div>
                        @else
                            <div class="card-header border-0 text-start text-md-center">
                                <h5 class="card-title">@lang('KYC Information')</h5>
                                <p>@lang('Verify your process instantly.')</p>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row">
                                    <div class="col-md-12 mx-auto">
                                        <div class="row g-4">
                                            @if($kyc->input_form)
                                                @foreach($kyc->input_form as $k => $v)
                                                    @if($v->type == "text")
                                                        <div class="col-12">
                                                            <label
                                                                class="form-label">{{trans($v->field_label)}} @if($v->validation == 'required')
                                                                    *
                                                                @endif</label>
                                                            <div class="form-group input-box">
                                                                <input type="text" name="{{$k}}"
                                                                       class="form-control"
                                                                       @if($v->validation == "required") required @endif>
                                                            </div>
                                                            @if ($errors->has($k))
                                                                <span
                                                                    class="text-danger">{{ trans($errors->first($k)) }}</span>
                                                            @endif
                                                        </div>
                                                    @elseif($v->type == "number")
                                                        <div class="col-12">
                                                            <label
                                                                class="form-label">{{trans($v->field_label)}} @if($v->validation == 'required')
                                                                    *
                                                                @endif</label>
                                                            <div class="form-group input-box">
                                                                <input type="number" name="{{$k}}"
                                                                       class="form-control"
                                                                       @if($v->validation == "required") required @endif>
                                                            </div>
                                                            @if ($errors->has($k))
                                                                <span
                                                                    class="text-danger">{{ trans($errors->first($k)) }}</span>
                                                            @endif
                                                        </div>

                                                    @elseif($v->type == "date")
                                                        <div class="col-12">
                                                            <label
                                                                class="form-label">{{trans($v->field_label)}} @if($v->validation == 'required')
                                                                    *
                                                                @endif</label>
                                                            <div class="form-group input-box">
                                                                <input type="date" name="{{$k}}"
                                                                       class="form-control"
                                                                       @if($v->validation == "required") required @endif>
                                                            </div>
                                                            @if ($errors->has($k))
                                                                <span
                                                                    class="text-danger">{{ trans($errors->first($k)) }}</span>
                                                            @endif
                                                        </div>

                                                    @elseif($v->type == "textarea")
                                                        <div class="col-12">
                                                            <label
                                                                class="form-label"><strong>{{trans($v->field_label)}} @if($v->validation == 'required')
                                                                        *
                                                                    @endif
                                                                </strong></label>
                                                            <div class="form-group input-box">
                                                            <textarea name="{{$k}}" class="form-control" rows="3"
                                                                      @if($v->validation == "required") required @endif></textarea>
                                                            </div>
                                                            @if ($errors->has($k))
                                                                <span
                                                                    class="text-danger">{{ trans($errors->first($k)) }}</span>
                                                            @endif
                                                        </div>
                                                    @elseif($v->type == "file")

                                                        <div class="col-12">
                                                            <label
                                                                class="form-label">{{trans($v->field_label)}} @if($v->validation == 'required')
                                                                    *
                                                                @endif</label>
                                                            <div class="image-input ">
                                                                <label for="image-upload" id="image-label2"><i
                                                                        class="fas fa-upload"></i></label>
                                                                <input type="file" name="{{$k}}"
                                                                       placeholder="Choose image" id="image2">
                                                                <img id="image_preview_container2" class="preview-image"
                                                                     src="{{getFile('dummy','dummy')}}"
                                                                     alt="preview image">
                                                            </div>
                                                            @if ($errors->has($k))
                                                                <br>
                                                                <span
                                                                    class="text-danger">{{ __($errors->first($k)) }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-btn-wrapper text-center text-md-left">
                                <button type="submit"
                                        class="btn-custom w-100">
                                    <span>@lang('Submit')</span></button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css-lib')
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

        $(document).on('click', '#image-label2', function () {
            $('#image2').trigger('click');
        });

        $(document).on('change', '#image2', function () {
            var _this = $(this);
            var newimage = new FileReader();
            newimage.readAsDataURL(this.files[0]);
            newimage.onload = function (e) {
                $('#image_preview_container2').attr('src', e.target.result);
            }
        });
    </script>
@endpush
