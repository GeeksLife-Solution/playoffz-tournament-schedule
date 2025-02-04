<div class="modal fade" id="newModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Add New')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('admin.storeTeam')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-dark">@lang('Game Category')</label>
                        <div class="tom-select-custom">
                            <select name="category" class="js-select form-control"
                                    data-live-search="true" required>
                                @foreach($categories as $key => $item)
                                    <option value="{{$item->id}}"
                                            data-option-template='<div class="d-flex align-items-start"><div class="flex-shrink-0">{{$item->icon}}</div><div class="flex-grow-1 ms-2"><span class="d-block fw-semibold">{{$item->name}}</span></div></div>'>
                                        {{$item->name}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('category')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-dark">@lang('Name')</label>
                        <input type="text" class="form-control" name="name" required>
                        @error('name')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-dark">@lang('Image')</label>
                        <label class="form-check form-check-dashed" for="logoUploader">
                            <img id="otherImg"
                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                 src="{{ getFile('local','abc', true) }}"
                                 alt="@lang("File Storage Logo")"
                                 data-hs-theme-appearance="default">

                            <img id="otherImg"
                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                 src="{{ getFile('local','abc', true) }}"
                                 alt="@lang("File Storage Logo")"
                                 data-hs-theme-appearance="dark">
                            <span class="d-block">@lang("Browse your file here")</span>
                            <input type="file" class="js-file-attach form-check-input"
                                   name="image" id="logoUploader"
                                   data-hs-file-attach-options='{
                                              "textTarget": "#otherImg",
                                              "mode": "image",
                                              "targetAttr": "src",
                                              "allowTypes": [".png", ".jpeg", ".jpg",".webp",".svg"]
                                           }'>
                        </label>
                        <strong>@lang('Image size should be '){{config('filelocation.team.size')}} @lang('for better resolution')</strong>
                        @error("image")
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="edit-status" class="text-dark"> @lang('Status') </label>
                        <div class="input-group input-group-sm-vertical">
                            <label class="form-control"
                                   for="editUserModalAccountTypeModalRadioEg1_1">
                                                        <span class="form-check">
                                                          <input type="radio" class="form-check-input"
                                                                 name="status" value="1"
                                                                 id="editUserModalAccountTypeModalRadioEg1_1" checked>
                                                          <span class="form-check-label">@lang('Active')</span>
                                                        </span>
                            </label>
                            <label class="form-control"
                                   for="editUserModalAccountTypeModalRadioEg1_2">
                                                        <span class="form-check">
                                                          <input type="radio" class="form-check-input"
                                                                 name="status" value="0"
                                                                 id="editUserModalAccountTypeModalRadioEg1_2">
                                                          <span class="form-check-label">@lang('Inactive')</span>
                                                        </span>
                            </label>
                        </div>
                        @error('status')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn-soft-success">@lang('Save')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Update Tournament')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>@lang('Game Category')</label>
                        <div class="tom-select-custom">
                            <select name="category" id="icon" class="js-select form-control"
                                    data-live-search="true" required>
                                @foreach($categories as $key => $item)
                                    <option value="{{$item->id}}"
                                            data-option-template='<div class="d-flex align-items-start"><div class="flex-shrink-0">{{$item->icon}}</div><div class="flex-grow-1 ms-2"><span class="d-block fw-semibold">{{$item->name}}</span></div></div>'
                                    >
                                        {{$item->name}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label>@lang('Name')</label>
                        <input type="text" class="form-control" name="name" value="" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>@lang('Image')</label>
                        <label class="form-check form-check-dashed" for="editUploader">
                            <img id="editImg"
                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2 editImage"
                                 src="{{ getFile('local','abc', true) }}"
                                 alt="@lang("File Storage Logo")"
                                 data-hs-theme-appearance="default">

                            <img id="editImg"
                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2 editImage"
                                 src="{{ getFile('local','abc', true) }}"
                                 alt="@lang("File Storage Logo")"
                                 data-hs-theme-appearance="dark">
                            <span class="d-block">@lang("Browse your file here")</span>
                            <input type="file" class="js-file-attach form-check-input"
                                   name="image" id="editUploader"
                                   data-hs-file-attach-options='{
                                              "textTarget": "#editImg",
                                              "mode": "image",
                                              "targetAttr": "src",
                                              "allowTypes": [".png", ".jpeg", ".jpg",".webp",".svg"]
                                           }'>
                        </label>
                        <strong>@lang('Image size should be '){{config('filelocation.team.size')}} @lang('for better resolution')</strong>
                        @error("image")
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="edit-status" class="text-dark"> @lang('Status') </label>
                        <div class="input-group input-group-sm-vertical">
                            <label class="form-control"
                                   for="editUserModalAccountTypeModalRadioEg1_1">
                                                        <span class="form-check">
                                                          <input type="radio" class="form-check-input"
                                                                 name="status" value="1"
                                                                 id="editUserModalAccountTypeModalRadioEg1_1">
                                                          <span class="form-check-label">@lang('Active')</span>
                                                        </span>
                            </label>
                            <label class="form-control"
                                   for="editUserModalAccountTypeModalRadioEg1_2">
                                                        <span class="form-check">
                                                          <input type="radio" class="form-check-input"
                                                                 name="status" value="0"
                                                                 id="editUserModalAccountTypeModalRadioEg1_2">
                                                          <span class="form-check-label">@lang('Inactive')</span>
                                                        </span>
                            </label>
                        </div>
                        @error('status')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn-soft-success">@lang('Update')</button>
                </div>
            </form>
        </div>
    </div>
</div>
