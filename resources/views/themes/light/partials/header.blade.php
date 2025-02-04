<!-- ======= Header ======= -->
<header id="header" class="header fixed-top">
    <div class="container-fluid container-xl d-flex align-items-center justify-content-between">
        <a href="#" class="logo d-flex align-items-center">
            <img src="{{ asset($themeTrue . 'img/logo.png') }}" alt="">
            <span>FlexStart</span>
        </a>
        <nav id="navbar" class="navbar">
            <ul>
                {!! renderHeaderMenu(getHeaderMenuData()) !!}
                @guest
                <li><a class="nav-link scrollto" href="{{ route("login") }}">Login</a></li>
                @endguest
                <li>
                    @auth
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                class="fal fa-sign-out-alt"></i>@lang('Sign Out')</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endauth
                </li>
            </ul>
            <i class="bi bi-list mobile-nav-toggle"></i>
        </nav>
    </div>
</header>
<!-- End Header -->
