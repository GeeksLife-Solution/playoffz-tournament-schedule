@extends($theme.'layouts.user')
@section('title', trans($title))

@section('content')
    <div class="row justify-content-between">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="title text-start m-0">@lang('Members List')</h5>
                    <a href="{{route('user.member.add')}}" class="btn btn-primary">Add Member</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped service-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">@lang('Name')</th>
                                    <th scope="col">@lang('Email')</th>
                                    <th scope="col">@lang('Status')</th>
                                    <th scope="col">@lang('Created At')</th>
                                    <th scope="col">@lang('Actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($data['member']) && $data['member']->count() > 0)
                                    @foreach($data['member'] as $member)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>@lang($member->name)</td>
                                            <td>{{ $member->email }}</td>
                                            <td>
                                                <span class="badge {{ $member->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                                    @lang($member->status == 1 ? 'Active' : 'Inactive')
                                                </span>
                                            </td>
                                            <td>{{ date('d M Y', strtotime($member->created_at)) }}</td>
                                            <td>
                                                @php
                                                    $route = route('user.member.update');
                                                    $params = ["member_id" => $member->id];
                                                    $encryptedUrl = encryptUrl($route, $params);

                                                    $delRoute = route('user.member.destroy');
                                                    $deleteUrl = encryptUrl($delRoute, $params);
                                                @endphp
                                                
                                                <button class="btn btn-sm btn-primary edit-btn w-auto" 
                                                    data-name="{{ $member->name }}"
                                                    data-email="{{ $member->email }}"
                                                    data-status="{{ $member->status }}"
                                                    data-url="{{ $encryptedUrl }}">
                                                    <i class="fas fa-edit"></i> @lang('Edit')
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-btn w-auto" data-url="{{$deleteUrl}}">
                                                    <i class="fas fa-trash"></i> @lang('Delete')
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">@lang('No members found.')</td>
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
                    <p>@lang('Are you sure you want to delete this member? This action cannot be undone.')</p>
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

    <!-- Edit Member Modal -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content  p-0">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Edit Member')</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0">
                    <form id="editForm" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group mb-2">
                                <label for="memberName">@lang('Name')</label>
                                <input type="text" class="form-control" id="memberName" name="name" required>
                            </div>
                            <div class="form-group mb-2">
                                <label for="memberEmail">@lang('Email')</label>
                                <input type="email" class="form-control" id="memberEmail" name="email" required>
                            </div>
                            <div class="form-group mb-2">
                                <label for="memberStatus">@lang('Status')</label>
                                <select class="form-control" id="memberStatus" name="status">
                                    <option value="1">@lang('Active')</option>
                                    <option value="0">@lang('Inactive')</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
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
            let memberName = $(this).data('name');
            let memberEmail = $(this).data('email');
            let memberStatus = $(this).data('status');
            let editUrl = $(this).data('url');

            // Populate Modal Fields
            $('#memberName').val(memberName);
            $('#memberEmail').val(memberEmail);
            $('#memberStatus').val(memberStatus);
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
    });
</script>
@endpush