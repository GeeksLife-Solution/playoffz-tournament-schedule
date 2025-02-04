@extends($theme.'layouts.user')
@section('title',trans($title))
@section('content')

    <div class="row justify-content-between ">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center">
                    <h5 class="title text-start m-0">@lang('Referral link')</h5>
                </div>
                <div class="card-body">
                    <p>
                        @lang('Automatically top up your account balance by sharing your referral link, Earn a percentage of whatever plan your referred user buys.')</p>
                    <div>
                        <form>
                            <div class="form-group">
                                <div class="input-group input-box">
                                    <input type="text"
                                           value="{{route('register.sponsor',[Auth::user()->username])}}"
                                           class="form-control" id="sponsorURL" readonly="">
                                    <div class="input-group-append">
                                            <span class="input-group-text form-control copytext" id="copyBoard"
                                                  onclick="copyFunction()">
                                                    <i class="fa fa-copy"></i>
                                            </span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            @if(0 < count($directReferralUsers))
                <div class="card ">
                    <div class="card-header">
                        <h5 class="title text-start m-0">@lang('Referral Members')</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-parent table-responsive m-0 mt-4">
                            <table class="table table-striped service-table" >
                                <thead>
                                <tr>
                                    <th scope="col">@lang('Username')</th>
                                    <th scope="col">@lang('Level')</th>
                                    <th scope="col">@lang('Email')</th>
                                    <th scope="col">@lang('Phone Number')</th>
                                    <th scope="col">@lang('Joined At')</th>
                                </tr>
                                </thead>
                                <tbody class="block-statistics">
                                @foreach($directReferralUsers as $user)
                                    @php
                                     $getDirectUsers = getDirectReferralUsers($user->id);
                                    @endphp
                                    <tr id="user-{{ $user->id }}" data-level="0" data-loaded="false">
                                        <td data-label="@lang('Username')">
                                            <a href="javascript:void(0)"
                                               class="{{ count($getDirectUsers) > 0 ? 'nextDirectReferral' : '' }}"
                                               data-id="{{ $user->id }}"
                                            >
                                                @if(count($getDirectUsers) > 0)
                                                    <i class="fas fa-arrow-down"></i>
                                                @endif
                                                @lang($user->username)
                                            </a>
                                        </td>
                                        <td data-label="@lang('Level')">
                                            @lang('Level 1')
                                        </td>
                                        <td data-label="@lang('Email')" class="">{{$user->email}}</td>
                                        <td data-label="@lang('Phone Number')">
                                            {{$user->phone}}
                                        </td>
                                        <td data-label="@lang('Joined At')">
                                            {{dateTime($user->created_at)}}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
@push('script')
    <script>
        "use strict";
        var loaderColor = "{{(session()->get('dark-mode') == 'true') ? '#233645' :  '#ffffff'}}";
        $(document).on('click', '.nextDirectReferral', function () {
            let _this = $(this);
            let parentRow = _this.closest('tr');

            // Check if the downline is already loaded
            if (parentRow.data('loaded')) {
                return;
            }

            getDirectReferralUser(_this);
        });

        function getDirectReferralUser(_this) {

            Notiflix.Block.standard('.block-statistics',{
                backgroundColor: loaderColor,
            });

            let userId = _this.data('id');
            let parentRow = _this.closest('tr');
            let currentLevel = parseInt(parentRow.data('level')) + 1;
            let downLabel = currentLevel + 1;

            setTimeout(function () {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "{{ route('user.myGetDirectReferralUser') }}",
                    method: 'POST',
                    data: {
                        userId: userId,
                    },
                    success: function (response) {

                        Notiflix.Block.remove('.block-statistics');
                        let directReferralUsers = response.data;

                        let referralData = '';

                        directReferralUsers.forEach(function (directReferralUser) {
                            referralData += `
                        <tr id="user-${directReferralUser.id}" data-level="${currentLevel}">
                            <td data-label="@lang('Username')" style="padding-left: ${currentLevel * 35}px;">
                                <a class="${directReferralUser.count_direct_referral > 0 ? 'nextDirectReferral' : ''}" href="javascript:void(0)" style="border-bottom: none !important;" data-id="${directReferralUser.id}">
                                    ${directReferralUser.count_direct_referral > 0 ? ' <i class="fas fa-arrow-down"></i>' : ''}
                                    ${directReferralUser.username}
                                </a>
                            </td>

                            <td data-label="@lang('Level')">
                                 <span>Level ${downLabel}</span>
                            </td>

                            <td data-label="@lang('Email')">
                                ${directReferralUser.email ? directReferralUser.email : '-'}
                            </td>
                            <td data-label="@lang('Phone Number')">
                                 ${directReferralUser.phone??'-'}
                            </td>

                            <td data-label="Joined At">
                                ${directReferralUser.joined_at}
                            </td>
                            </tr>`;
                        });

                        // Mark this row as having its downline loaded
                        parentRow.data('loaded', true);

                        $(`#user-${userId}`).after(referralData);
                    },
                    error: function (xhr, status, error) {
                        console.log(error);
                    }
                });
            }, 100);
        }


        function copyFunction() {
            var copyText = document.getElementById("sponsorURL");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            /*For mobile devices*/
            document.execCommand("copy");
            Notiflix.Notify.Success(`Copied: ${copyText.value}`);
        }
    </script>
@endpush
