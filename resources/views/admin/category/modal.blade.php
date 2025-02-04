<div class="modal fade" id="newModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Add New')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('admin.storeCategory')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-dark">@lang('Title')</label>
                        <input type="text" class="form-control" name="title" required>
                        @error('title')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-dark">@lang('Icon')</label>
                        <div class="tom-select-custom">
                            <select name="icon" class="js-select form-control"
                                    data-live-search="true" required>
                                @foreach($games as $key => $value)
                                    <option value="{{$value}}"
                                            data-option-template='<div class="d-flex align-items-start"><div class="flex-shrink-0">{{$value}}</div><div class="flex-grow-1 ms-2"><span class="d-block fw-semibold">{{$key}}</span></div></div>'>
                                        {{$key}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('icon')
                        <span class="text-danger">{{ $message }}</span>
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
                <h5 class="modal-title">@lang('Update Category')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                @csrf
                <input type="hidden" name="key" value="social.item">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>@lang('Title')</label>
                        <input type="text" class="form-control" name="title" value="" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>@lang('Icon')</label>
                        <div class="tom-select-custom">
                            <select name="icon" id="icon" class="js-select form-control"
                                    data-live-search="true">
                                @foreach($games as $key => $value)
                                    <option value="{{$value}}"
                                            data-option-template='<div class="d-flex align-items-start"><div class="flex-shrink-0">{{$value}}</div><div class="flex-grow-1 ms-2"><span class="d-block fw-semibold">{{$key}}</span></div></div>'
                                            >
                                        {{$key}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
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
