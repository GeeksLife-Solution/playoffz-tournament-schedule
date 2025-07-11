<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="shortcut icon" href="{{getFile(basicControl()->favicon_driver, basicControl()->favicon)}}"
          type="image/x-icon">
    <title>{{basicControl()->site_title}} | @yield('title')</title>
    <link rel="stylesheet" type="text/css" href="{{ asset($themeTrue . 'css/bootstrap.min.css') }}"/>
    @stack('css-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset($themeTrue . 'css/animate.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset($themeTrue . 'css/owl.carousel.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset($themeTrue . 'css/owl.theme.default.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset($themeTrue . 'css/skitter.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset($themeTrue . 'css/aos.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset($themeTrue . 'css/jquery.fancybox.min.css') }}"/>

    <script src="{{ asset('assets/admin/js/fontawesome/fontawesomepro.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset($themeTrue . 'css/style.css') }}"/>

    @stack('style')

</head>

<body @if(!session()->get('dark-mode')) @if(basicControl()->default_mode) class="dark-mode"
      @endif @endif @if(session()->get('dark-mode') == 'true') class="dark-mode" @endif>


<!-- navbar -->
@include($theme.'partials.nav')
<!-- wrapper -->
<div class="wrapper">
    <!-- leftbar -->
    <div class="leftbar" id="userPanelSideBar">
        <div class="px-2 d-lg-none">
            <button
                class="remove-class-btn light btn-custom"
                onclick="removeClass('userPanelSideBar')">
                <i class="fal fa-chevron-left"></i>@lang('Back')
            </button>
        </div>
        <div class="top profile">
            <h4 class="d-flex justify-content-between p-2">
                @lang('Profile')
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                >
                    <button class="btn-custom light">
                        <i class="fal fa-sign-out-alt"></i>
                    </button>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </h4>
            <img src="{{getFile(auth()->user()->image_driver,auth()->user()->image)}}" alt="..."/>
            <h5> {{auth()->user()->username}}</h5>
        </div>
        <ul class="main">
            <li>
                <a href="{{route('user.dashboard')}}" class="{{menuActive('user.dashboard')}}">
                    <i class="fal fa-tachometer-alt"></i> <!-- More appropriate dashboard icon -->
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{route('user.schedule.list')}}" class="{{menuActive('user.schedule.list')}}">
                    <i class="fal fa-calendar-alt"></i> <!-- Calendar icon for schedules -->
                    Schedules
                </a>
            </li>
            <li>
                <a href="{{route('user.groups.list')}}" class="{{menuActive('user.groups.list')}}">
                    <i class="fal fa-users"></i> <!-- People icon for groups -->
                    Groups
                </a>
            </li>
            <li>
                <a href="{{route('user.courts.list')}}" class="{{menuActive('user.courts.list')}}">
                    <i class="fal fa-map-marked-alt"></i> <!-- Location icon for courts -->
                    Courts
                </a>
            </li>
            <li>
                <a href="{{route('user.registrations')}}" class="{{menuActive('user.registrations')}}">
                    <i class="fal fa-clipboard-list"></i> <!-- Clipboard icon for registrations -->
                    Registrations
                </a>
            </li>
            <li>
                <a href="{{route('user.member.list')}}" class="{{menuActive('user.member.list')}}">
                    <i class="fal fa-user-friends"></i> <!-- Multiple users icon for members -->
                    Members
                </a>
            </li>
            <li>
                <a href="{{route('user.waiver.list')}}" class="{{menuActive('user.waiver.list')}}">
                    <i class="fal fa-file-signature"></i> <!-- Document with signature icon for waivers -->
                    Waivers
                </a>
            </li>
        </ul>
    </div>
    <div class="content user-panel">
        <div class="d-flex justify-content-between">
            <div>
                <h4>@yield('title')</h4>
            </div>
            <button
                class="btn-custom light toggle-user-panel-sidebar d-lg-none"
                onclick="toggleSidebar('userPanelSideBar')">
                <i class="fal fa-sliders-h"></i>
            </button>
        </div>
        @yield('content')
    </div>

</div>


@stack('loadModal')


<script src="{{ asset($themeTrue . 'js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset($themeTrue . 'js/jquery-3.6.0.min.js') }}"></script>

@stack('extra-js')

{{--<script src="{{ asset($themeTrue . 'js/fontawesome.min.js') }}"></script>--}}
<script src="{{ asset($themeTrue . 'js/owl.carousel.min.js') }}"></script>
<script src="{{ asset($themeTrue . 'js/masonry.pkgd.min.js') }}"></script>
<script src="{{ asset($themeTrue . 'js/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset($themeTrue . 'js/jquery.counterup.min.js') }}"></script>
<script src="{{ asset($themeTrue . 'js/jquery.easing.1.3.js') }}"></script>
<script src="{{ asset($themeTrue . 'js/jquery.skitter.min.js') }}"></script>
<script src="{{ asset($themeTrue . 'js/aos.js') }}"></script>
<script src="{{ asset($themeTrue . 'js/jquery.fancybox.min.js') }}"></script>
<script src="{{ asset($themeTrue . 'js/script.js') }}"></script>


<script src="{{asset('assets/global/js/notiflix-aio-3.2.6.min.js')}}"></script>
<script src="{{asset('assets/global/js/pusher.min.js')}}"></script>
<script src="{{asset('assets/global/js/vue.min.js')}}"></script>
<script src="{{asset('assets/global/js/axios.min.js')}}"></script>

@include('plugins')
@auth
    @if(basicControl()->in_app_notification == 1)
        <script>
            'use strict';
            let pushNotificationArea = new Vue({
                el: "#pushNotificationArea",
                data: {
                    items: [],
                },
                mounted() {
                    this.getNotifications();
                    this.pushNewItem();
                },
                methods: {
                    getNotifications() {
                        let app = this;
                        axios.get("{{ route('user.push.notification.show') }}")
                            .then(function (res) {
                                app.items = res.data;
                            })
                    },
                    readAt(id, link) {
                        let app = this;
                        let url = "{{ route('user.push.notification.readAt', 0) }}";
                        url = url.replace(/.$/, id);
                        axios.get(url)
                            .then(function (res) {
                                if (res.status) {
                                    app.getNotifications();
                                    if (link != '#') {
                                        window.location.href = link
                                    }
                                }
                            })
                    },
                    readAll() {
                        let app = this;
                        let url = "{{ route('user.push.notification.readAll') }}";
                        axios.get(url)
                            .then(function (res) {
                                if (res.status) {
                                    app.items = [];
                                }
                            })
                    },
                    pushNewItem() {
                        let app = this;
                        // Pusher.logToConsole = true;
                        let pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
                            encrypted: true,
                            cluster: "{{ env('PUSHER_APP_CLUSTER') }}"
                        });
                        let channel = pusher.subscribe('user-notification.' + "{{ Auth::id() }}");
                        channel.bind('App\\Events\\UserNotification', function (data) {
                            app.items.unshift(data.message);
                        });
                        channel.bind('App\\Events\\UpdateUserNotification', function (data) {
                            app.getNotifications();
                        });
                    }
                }
            });
        </script>
    @endif
@endauth
@stack('script')


@include($theme.'partials.notification')
<script>
    $(document).ready(function () {
        $(".language").find("select").change(function () {
            window.location.href = "{{route('language')}}/" + $(this).val()
        })
    })

    const darkMode = () => {
        var $theme = document.body.classList.toggle("dark-mode");
        $.ajax({
            url: "{{ route('themeMode') }}/" + $theme,
            type: 'get',
            success: function (response) {
                console.log(response);
            }
        });
    };
</script>

</body>
</html>
