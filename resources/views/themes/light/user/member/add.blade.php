@extends($theme.'layouts.user')
@section('title',trans($title))
@section('content')
    <div class="row justify-content-between ">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-center">
                    <h5 class="title text-start m-0">@lang('Add Member')</h5>
                </div>
                <div class="card-body">
                    <p>
                        @lang("Your new member will receive an email invite to join Test if they aren't already registered on Playpass.")</p>
                    <div>
                        <form action="{{route('user.member.store')}}" method="POST">
                            @csrf
                            <div class="form-group mb-2">
                                <label for="team_number">Name <span class="text-danger">*</span></label>
                                <div class="input-group input-box">
                                    <input type="text" value="" name="name" placeholder="Enter Name" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label for="team_number">Email <span class="text-danger">*</span></label>
                                <div class="input-group input-box">
                                    <input type="email" value="" name="email" placeholder="Enter Email" class="form-control" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary mt-2">Add Member</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection