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
                        <form action="{{ route('user.waiver.store') }}" method="POST" enctype="multipart/form-data">
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
                                <button class="btn btn-primary mt-2">Add Member</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fileInput = document.getElementById("fileInput");
            const textAreaInput = document.getElementById("textAreaInput");
            
            document.querySelectorAll("input[name='waiver[attachment_method]']").forEach(input => {
                input.addEventListener("change", function () {
                    if (this.value === "file") {
                        fileInput.style.display = "block";
                        textAreaInput.style.display = "none";
                        textAreaInput.value = "";
                    } else {
                        fileInput.style.display = "none";
                        textAreaInput.style.display = "block";
                        fileInput.value = "";
                    }
                });
            });
        });
    </script>
@endpush