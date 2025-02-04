{{-- Edit MODAL --}}
<div id="editModal" class="modal fade" data-bs-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Edit Question')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" class="questionId" name="questionId" value="">
                    <div class="form-group mb-3">
                        <label>@lang('Name')</label>
                        <input type="text" class="form-control editName mt-2" name="name" value="" required>
                        @error('name')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-dark">@lang('Status') </label>
                        <select id="editStatus" class="form-control editStatus mt-2"
                                name="status" required>
                            <option value="">@lang('Select Status')</option>
                            <option value="1">@lang('Active')</option>
                            <option value="0">@lang('Pending')</option>
                            <option value="2">@lang('Closed')</option>
                        </select>
                        @error('status')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>@lang('End Date')</label>
                        <input type="datetime-local" class="form-control editTime mt-2" name="end_time"
                               id="editEndDate" required>
                        @error('end_time')
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

{{-- Refund MODAL --}}
<div id="refundQuestionModal" class="modal fade" data-bs-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Refund Confirmation')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p>@lang('Are you sure to refund this?')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn-soft-success">@lang('Yes')</button>
                </div>
            </form>
        </div>
    </div>
</div>
