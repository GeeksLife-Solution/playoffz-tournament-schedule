@extends($theme.'layouts.user')
@section('title',trans('Dashboard'))
@section('content')
    <div class="section dashboard">
        <div class="row" id="firebase-app">
            <div v-if="user_foreground == '1' || user_background == '1'" class="card-box">
                <div v-if="notificationPermission == 'default' && !is_notification_skipped" v-cloak>
                    <div class="media align-items-center d-flex justify-content-between alert alert-warning mb-4">
                        <div><i class="fas fa-exclamation-triangle"></i> @lang('Do not miss any single important notification! Allow your browser to get instant push notification')
                            <button class="btn-custom ml-3" id="allow-notification">@lang('Allow me')</button>
                        </div>
                        <button class="close-btn pt-1" @click.prevent="skipNotification"><i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-box" v-if="notificationPermission == 'denied' && !is_notification_skipped" v-cloak>
                <div class="media align-items-center d-flex justify-content-between alert alert-warning mb-4">
                    <div><i class="fas fa-exclamation-triangle"></i> @lang('Please allow your browser to get instant push notification. Allow it from notification setting.')
                    </div>
                    <button class="close-btn pt-1" @click.prevent="skipNotification"><i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- contents -->
    <div class="row">
        <div class="col-xl-3 col-lg-4 col-md-3 col-sm-10 mb-2">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price"><sup>{{basicControl()->currency_symbol}}</sup>{{Auth()->user()->balance}}</h2>
                    <p class="info">@lang('Total Balance')</p>
                </div>
                <div class="dashboard__card-icon">
                    <img src="{{asset($themeTrue.'images/icon/wallet.png')}}" alt="...">
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-3 col-sm-10 mb-2">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price"><sup>{{basicControl()->currency_symbol}}</sup>{{$userBet['totalInvest']??0}}</h2>
                    <p class="info">@lang('Total Bet Amount')</p>
                </div>
                <div class="dashboard__card-icon">
                    <img src="{{asset($themeTrue.'images/icon/invest.png')}}" alt="...">
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-3 col-sm-10 mb-2">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$userBet['totalBet']}}</h2>
                    <p class="info">@lang('Total Bet')</p>
                </div>
                <div class="dashboard__card-icon">
                    <img src="{{asset($themeTrue.'images/icon/bet.png')}}" alt="...">
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-md-3 col-sm-10 mb-2">
            <div class="dashboard__card">
                <div class="dashboard__card-content">
                    <h2 class="price">{{$userBet['win']}}</h2>
                    <p class="info">@lang('Bet Win')</p>
                </div>
                <div class="dashboard__card-icon">
                    <img src="{{asset($themeTrue.'images/icon/earn.png')}}" alt="...">
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-6 col-sm-12">
            <div id="container" class="apexcharts-canvas"></div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body ">
                    <div class="table-parent table-responsive m-0">
                        <table class="table  table-striped" id="service-table">
                            <thead>
                            <tr>
                                <th>@lang('SL No.')</th>
                                <th>@lang('Invest Amount')</th>
                                <th>@lang('Return Amount')</th>
                                <th>@lang('Charge')</th>
                                <th>@lang('Ratio')</th>
                                <th class="text-center">@lang('Status')</th>
                                <th class="text-center">@lang('Information')</th>
                                <th>@lang('Time')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($betInvests as $key => $item)
                                <tr>
                                    <td data-label="@lang('SL No.')">{{++$key}}</td>
                                    <td data-label="@lang('Invest Amount')">{{currencyPosition($item->invest_amount)}}</td>
                                    <td data-label="@lang('Return Amount')">{{currencyPosition($item->return_amount)}}</td>
                                    <td data-label="@lang('Charge')">{{currencyPosition($item->charge)}}</td>
                                    <td data-label="@lang('Ratio')"> @lang($item->ratio)</td>
                                    <td data-label="@lang('Status')" class="text-center">
                                        @if($item->status == 0)
                                            <span class="badge bg-warning">@lang('Processing')</span>
                                        @elseif($item->status == 1)
                                            <span class="badge bg-success">@lang('Win')</span>
                                        @elseif($item->status == -1)
                                            <span class="badge bg-danger">@lang('Loss')</span>
                                        @elseif($item->status == 2)
                                            <span class="badge bg-primary">@lang('Refund')</span>
                                        @endif

                                    </td>
                                    <td data-label="@lang('Information')" class="text-center">
                                        <button type="button" data-resource="{{$item->betInvestLog}}"
                                                data-bs-target="#investLogList" data-bs-toggle="modal"
                                                class="action-btn investLogList">
                                            <i class="fa fa-info-circle"></i></button>
                                    </td>
                                    <td data-label="@lang('Time')">
                                        {{ dateTime($item->created_at, 'd M Y h:i A') }}
                                    </td>
                                </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="100%">{{__('No Data Found!')}}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="investLogList" role="dialog">
        <div class="modal-dialog  modal-xl">
            <div class="modal-custom-content">
                <div class="modal-header modal-colored-header">
                    <h5 class="modal-title service-title">@lang('Information')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped" id="service-table">
                        <thead class="thead-dark">
                        <tr>
                            <th>@lang('#')</th>
                            <th>@lang('Match Name')</th>
                            <th>@lang('Category Name')</th>
                            <th>@lang('Tournament Name')</th>
                            <th>@lang('Question Name')</th>
                            <th>@lang('Option Name')</th>
                            <th>@lang('Ratio')</th>
                            <th>@lang('Result')</th>
                        </tr>
                        </thead>
                        <tbody class="result-body">

                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-custom me-2 mb-2"
                            data-bs-dismiss="modal"><span>@lang('Close')</span></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset($themeTrue.'js/apexcharts.js')}}"></script>
    <script>
        'use strict'
        $(document).on('click', '.investLogList', function () {
            var obj = $(this).data('resource');
            var output = [];
            if (0 < obj.length) {
                obj.map(function (obj, i) {
                    var tr =
                        `<tr>
                        <td data-label="@lang('#')">${++i}</td>
                        <td data-label="@lang('Match Name')">${(obj).match_name}</td>
                        <td data-label="@lang('Category Name')">${obj.category_icon} ${obj.category_name}</td>
                        <td data-label="@lang('Tournament Name')">${obj.tournament_name}</td>
                        <td data-label="@lang('Question Name')">${obj.question_name}</td>
                        <td data-label="@lang('Option Name')">${obj.option_name}</td>
                        <td data-label="@lang('Ratio')">${obj.ratio}</td>
                        <td data-label="@lang('Result')">
                            ${(obj.status == '0') ? ` <span class='badge bg-warning'>@lang('Processing')</span>` : ''}
                            ${(obj.status == '2') ? ` <span class='badge bg-success'>@lang('Win')</span>` : ''}
                            ${(obj.status == '-2') ? ` <span class='badge bg-danger'>@lang('Lose')</span>` : ''}
                            ${(obj.status == '3') ? ` <span class='badge bg-secondary'>@lang('Refunded')</span>` : ''}

                        </td>
                    </tr>`;

                    output[i] = tr;
                });

            } else {
                output[0] = `
                        <tr>
                            <td colspan="100%" class=""text-center>@lang('No Data Found')</td>
                        </tr>`;
            }

            $('.result-body').html(output);
        });

        var options = {
            theme: {
                mode: '{{(session()->get('dark-mode') == 'true' || basicControl()->default_mode == 1) ? 'dark' :  ''}}',
            },
            series: [
                {
                    name: "{{trans('Invested')}}",
                    color: '{{basicControl()->primary_color}}',
                    data: {!! $dailyPayout->flatten() !!}
                },

            ],
            chart: {
                type: 'bar',
                // height: ini,
                background: '{{(session()->get('dark-mode') == 'true' || basicControl()->default_mode == 1) ? '#294056' :  '#ffffff'}} ',
                toolbar: {
                    show: false
                }

            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: {!! $dailyPayout->keys() !!},

            },
            yaxis: {
                title: {
                    text: ""
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                colors: ['#000'],
                y: {
                    formatter: function (val) {
                        return val + " {{config('basic.currency')}}"
                    }
                }
            }
        };
        var chart = new ApexCharts(document.querySelector("#container"), options);
        chart.render();

    </script>
@endpush
@if ($firebaseNotify)
    @push('script')
        <script type="module">
            import {
                initializeApp
            } from "https://www.gstatic.com/firebasejs/9.17.1/firebase-app.js";
            import {
                getMessaging,
                getToken,
                onMessage
            } from "https://www.gstatic.com/firebasejs/9.17.1/firebase-messaging.js";

            const firebaseConfig = {
                apiKey: "{{ $firebaseNotify['apiKey'] }}",
                authDomain: "{{ $firebaseNotify['authDomain'] }}",
                projectId: "{{ $firebaseNotify['projectId'] }}",
                storageBucket: "{{ $firebaseNotify['storageBucket'] }}",
                messagingSenderId: "{{ $firebaseNotify['messagingSenderId'] }}",
                appId: "{{ $firebaseNotify['appId'] }}",
                measurementId: "{{ $firebaseNotify['measurementId'] }}"
            };

            const app = initializeApp(firebaseConfig);
            const messaging = getMessaging(app);
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('{{ getProjectDirectory() }}' + `/firebase-messaging-sw.js`, {
                    scope: './'
                }).then(function(registration) {
                    requestPermissionAndGenerateToken(registration);
                }).catch(function(error) {});
            } else {}

            onMessage(messaging, (payload) => {
                if (payload.data.foreground || parseInt(payload.data.foreground) == 1) {
                    const title = payload.notification.title;
                    const options = {
                        body: payload.notification.body,
                        icon: payload.notification.icon,
                    };
                    new Notification(title, options);
                }
            });

            function requestPermissionAndGenerateToken(registration) {
                document.addEventListener("click", function(event) {
                    if (event.target.id == 'allow-notification') {
                        Notification.requestPermission().then((permission) => {
                            if (permission === 'granted') {
                                getToken(messaging, {
                                    serviceWorkerRegistration: registration,
                                    vapidKey: "{{ $firebaseNotify['vapidKey'] }}"
                                })
                                    .then((token) => {
                                        $.ajax({
                                            url: "{{ route('user.save.token') }}",
                                            method: "post",
                                            data: {
                                                token: token,
                                            },
                                            success: function(res) {}
                                        });
                                        window.newApp.notificationPermission = 'granted';
                                    });
                            } else {
                                window.newApp.notificationPermission = 'denied';
                            }
                        });
                    }
                });
            }
        </script>
        <script>
            window.newApp = new Vue({
                el: "#firebase-app",
                data: {
                    user_foreground: '',
                    user_background: '',
                    notificationPermission: Notification.permission,
                    is_notification_skipped: sessionStorage.getItem('is_notification_skipped') == '1'
                },
                mounted() {
                    sessionStorage.clear();
                    this.user_foreground = "{{ $firebaseNotify['user_foreground'] }}";
                    this.user_background = "{{ $firebaseNotify['user_background'] }}";
                },
                methods: {
                    skipNotification() {
                        sessionStorage.setItem('is_notification_skipped', '1');
                        this.is_notification_skipped = true;
                    }
                }
            });
        </script>
    @endpush
@endif
