@extends($theme.'layouts.user')
@section('title',trans($title))
@section('content')
    <div class="row justify-content-between ">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-center">
                    <h5 class="title text-start m-0">@lang("Let's make your first registration activity")</h5>
                </div>
                <div class="card-body">
                    <p>Save time with online registration and accept payments online. It's quick to set up price options and start receiving payouts in your bank. Tap the big button to get started.</p>
                    <a target="_blank" href="https://app.playoffz.in/register.php" class="btn btn-primary">Create Registration Activity</a>
                </div>
            </div>
        </div>
    </div>
@endsection
