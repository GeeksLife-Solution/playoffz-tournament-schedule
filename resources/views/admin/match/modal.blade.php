<div class="modal fade" id="newModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Add Match')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('admin.storeMatch')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('Game Category')</label>
                                <div class="mt-2">
                                    <select name="category" class="form-select select2" id="category" required>
                                        <option value="" selected disabled>@lang('Select Game Category')</option>
                                        @foreach($categories as $key => $item)
                                            <option value="{{$item->id}}">
                                                {{$item->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('category')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('Game Tournament')</label>
                                <div class="mt-2">
                                    <select name="tournament" class="form-select select2"
                                            data-live-search="true" required>

                                    </select>
                                </div>
                                @error('tournament')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="text-dark">@lang('Match Name (optional)')</label>
                                <input type="text" class="form-control mt-2" name="name">
                                @error('name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('Team 01')</label>
                                <div class="mt-2">
                                    <select name="team1" class="form-select select2"
                                            data-live-search="true" required>

                                    </select>
                                </div>
                                @error('team1')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('Team 02')</label>
                                <div class="mt-2">
                                    <select name="team2" class="form-select select2"
                                            data-live-search="true" required>

                                    </select>
                                </div>
                                @error('team2')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('Start Date')</label>
                                <input type="datetime-local" class="form-control mt-2" name="start_date" required>
                                @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('End Date')</label>
                                <input type="datetime-local" class="form-control mt-2" name="end_date" required>
                                @error('end_date')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit-status" class="text-dark"> @lang('Status') </label>
                        <div class="input-group input-group-sm-vertical mt-2">
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Update Match')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('Game Category')</label>
                                <div class="mt-2">
                                    <select name="category" class="form-select select2 editCategory" required>
                                        <option value="" selected disabled>@lang('Select Game Category')</option>
                                        @foreach($categories as $key => $item)
                                            <option value="{{$item->id}}">
                                                {{$item->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('category')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('Game Tournament')</label>
                                <div class="mt-2">
                                    <select name="tournament" class="form-select select2" id="editTournament" required>

                                    </select>
                                </div>
                                @error('tournament')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="text-dark">@lang('Match Name (optional)')</label>
                                <input type="text" class="form-control mt-2" name="name">
                                @error('name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('Team 01')</label>
                                <div class="mt-2">
                                    <select name="team1" class="form-select select2"
                                            data-live-search="true" id="editTeam1" required>

                                    </select>
                                </div>
                                @error('team1')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('Team 02')</label>
                                <div class="mt-2">
                                    <select name="team2" class="form-select select2" id="editTeam2"
                                            data-live-search="true" required>

                                    </select>
                                </div>
                                @error('team2')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('Start Date')</label>
                                <input type="datetime-local" class="form-control mt-2" id="editStartDate" name="start_date" required>
                                @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-dark">@lang('End Date')</label>
                                <input type="datetime-local" class="form-control mt-2" id="editEndDate" name="end_date" required>
                                @error('end_date')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit-status" class="text-dark"> @lang('Status') </label>
                        <div class="input-group input-group-sm-vertical mt-2">
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
