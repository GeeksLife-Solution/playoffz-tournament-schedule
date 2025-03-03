@extends($theme.'layouts.user')
@section('title', trans($title))

@section('content')
    <div class="row justify-content-between">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="title text-start m-0">@lang('Waivers List')</h5>
                    <a href="{{ route('user.waiver.create') }}" class="btn btn-primary">@lang('Add Waiver')</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped service-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">@lang('Name')</th>
                                    <th scope="col">@lang('Signature Method')</th>
                                    <th scope="col">@lang('Status')</th>
                                    <th scope="col">@lang('Created At')</th>
                                    <th scope="col">@lang('Actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($data['waiver']->count() > 0)
                                    @foreach($data['waiver'] as $waiver)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $waiver->name }}</td>
                                            <td>@lang(ucfirst($waiver->signature))</td>
                                            <td>
                                                <span class="badge {{ $waiver->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                                    @lang($waiver->status == 1 ? 'Active' : 'Inactive')
                                                </span>
                                            </td>
                                            <td>{{ date('d M Y', strtotime($waiver->created_at)) }}</td>
                                            <td>
                                                @php
                                                    $route = route('user.waiver.update');
                                                    $params = ["waiver_id" => $waiver->id];
                                                    $encryptedUrl = encryptUrl($route, $params);

                                                    $delRoute = route('user.waiver.destroy');
                                                    $deleteUrl = encryptUrl($delRoute, $params);
                                                @endphp
                                                
                                                <button class="btn btn-sm btn-primary edit-btn w-auto"
                                                    data-attachment="{{ $waiver->attachment ? asset('assets/upload/' . $waiver->attachment) : '' }}"
                                                    data-id="{{ $waiver->id }}"
                                                    data-name="{{ $waiver->name }}"
                                                    data-content="{{ $waiver->content }}"
                                                    data-signature="{{ $waiver->signature }}"
                                                    data-status="{{ $waiver->status }}"
                                                    data-url="{{ $encryptedUrl }}">
                                                    <i class="fas fa-edit"></i> @lang('Edit')
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-btn w-auto" data-url="{{ $deleteUrl }}">
                                                    <i class="fas fa-trash"></i> @lang('Delete')
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">@lang('No waivers found.')</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirm Deletion')</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>@lang('Are you sure you want to delete this waiver? This action cannot be undone.')</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">@lang('Delete')</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Cancel')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Waiver Modal -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content p-0">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Edit Waiver')</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- Waiver Name -->
                        <div class="form-group mb-2">
                            <label for="waiver_name">Waiver Name <span class="text-danger">*</span></label>
                            <div class="input-group input-box">
                                <input type="text" name="name" id="waiver_name" placeholder="Enter Name" class="form-control" required>
                            </div>
                        </div>
                        
                        <!-- Attach Waiver -->
                        <div class="form-group mb-2">
                            <label for="waiver_attachment">Attach Waiver <span class="text-danger">*</span></label>
                            <div class="input-group input-box">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="waiver[attachment_method]" id="uploadFile" value="file" checked required>
                                    <label class="form-label" for="uploadFile">Upload a file</label>
                                </div>
                                <div class="form-check ms-3">
                                    <input class="form-check-input" type="radio" name="waiver[attachment_method]" id="textAreaOption" value="textarea" required>
                                    <label class="form-label" for="textAreaOption">Enter text</label>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <input type="file" name="waiver[file]" class="form-control mt-2" id="fileInput">
                                <div id="filePreview"></div>
                                <textarea name="waiver[text]" class="form-control mt-2" id="textAreaInput" style="display: none;" placeholder="Enter waiver text"></textarea>
                            </div>
                        </div>
                        
                        <!-- Signature Method -->
                        <div class="form-group mb-2">
                            <label for="waiver_sign_method">Signature Method <span class="text-danger">*</span></label>
                            <select required class="form-control form-select" name="waiver[sign_method]" id="waiver_sign_method">
                                <option value="">How will players sign this waiver?</option>
                                <option value="checkbox">Check a box</option>
                                <option value="initials">Type their initials</option>
                                <option value="name">Type their name</option>
                                <option value="upload">Upload a signed copy</option>
                            </select>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">@lang('Save Changes')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Cancel')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    $(document).ready(function () {
        // Handle Edit Button Click
        $('.edit-btn').on('click', function () {
            let waiverId = $(this).data('id');
            let waiverName = $(this).data('name');
            let waiverContent = $(this).data('content');
            let waiverSignature = $(this).data('signature');
            let waiverStatus = $(this).data('status');
            let editUrl = $(this).data('url');
            let fileUrl = $(this).data('attachment'); // URL of the uploaded file

            // Populate Modal Fields
            $('#waiver_name').val(waiverName);
            $('#waiver_sign_method').val(waiverSignature);

            // Handle File/Text input correctly
            if (waiverContent) {
                $('#textAreaOption').prop('checked', true).trigger('change');
                $('#textAreaInput').val(waiverContent);
            } else {
                $('#uploadFile').prop('checked', true).trigger('change');
            }

            // Handle File Preview or Link
            if (fileUrl) {
                let fileExtension = fileUrl.split('.').pop().toLowerCase();
                let imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (imageExtensions.includes(fileExtension)) {
                    $('#filePreview').html(`<img src="${fileUrl}" alt="Waiver Image" class="img-thumbnail mt-2" style="max-width: 200px;">`);
                } else {
                    $('#filePreview').html(`<a href="${fileUrl}" target="_blank" class="btn btn-link mt-2">@lang('View File')</a>`);
                }
            } else {
                $('#filePreview').html('');
            }

            // Set form action URL
            $('#editForm').attr('action', editUrl);

            // Show Modal
            $('#editModal').modal('show');
        });

        // Handle Delete Button Click
        $('.delete-btn').on('click', function () {
            let deleteUrl = $(this).data('url');
            $('#deleteForm').attr('action', deleteUrl);
            $('#deleteModal').modal('show');
        });

        // Handle File/Text Selection Toggle
        $("input[name='waiver[attachment_method]']").on('change', function () {
            if (this.value === "file") {
                $("#fileInput").show();
                $("#textAreaInput").hide().val('');
                $('#filePreview').html('');
            } else {
                $("#fileInput").hide().val('');
                $("#textAreaInput").show();
                $('#filePreview').html('');
            }
        });
    });
</script>
@endpush