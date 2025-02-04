<div class="col-sm-4">
    <div class="card secbg br-4">
        <div class="card-body br-4">
            <form method="post" action="{{ route('user.updateProfile') }}"
                  enctype="multipart/form-data">
                <div class="form-group">
                    @csrf
                    <div class="image-input ">
                        <label for="image-upload" id="image-label"><i
                                class="fas fa-upload"></i></label>
                        <input type="file" name="image" placeholder="Choose image" id="image">
                        <img id="image_preview_container" class="preview-image"
                             src="{{getFile(auth()->user()->image_driver,auth()->user()->image)}}"
                             alt="preview image">
                    </div>
                    @error('image')
                    <span class="text-danger">{{$message}}</span>
                    @enderror

                </div>
                <h3>@lang(ucfirst(auth()->user()->name))</h3>
                <p>@lang('Joined At') @lang(auth()->user()->created_at->format('d M, Y g:i A'))</p>
                <div class="submit-btn-wrapper text-center text-md-left">
                    <button type="submit" class="btn-custom w-100">
                        <span>@lang('Image Update')</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
