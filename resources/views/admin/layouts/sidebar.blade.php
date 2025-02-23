<!-- Navbar Vertical -->
<aside
    class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-vertical-aside-initialized
    {{in_array(session()->get('themeMode'), [null, 'auto'] )?  'navbar-dark bg-dark ' : 'navbar-light bg-white'}}">
    <div class="navbar-vertical-container">
        <div class="navbar-vertical-footer-offset">
            <!-- Logo -->
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}" aria-label="{{ $basicControl->site_title }}">
                <img class="navbar-brand-logo navbar-brand-logo-auto"
                     src="{{ getFile(session()->get('themeMode') == 'auto'?$basicControl->admin_dark_mode_logo_driver : $basicControl->admin_logo_driver, session()->get('themeMode') == 'auto'?$basicControl->admin_dark_mode_logo:$basicControl->admin_logo, true) }}"
                     alt="{{ $basicControl->site_title }} Logo"
                     data-hs-theme-appearance="default">

                <img class="navbar-brand-logo"
                     src="{{ getFile($basicControl->admin_dark_mode_logo_driver, $basicControl->admin_dark_mode_logo, true) }}"
                     alt="{{ $basicControl->site_title }} Logo"
                     data-hs-theme-appearance="dark">

                <img class="navbar-brand-logo-mini"
                     src="{{ getFile($basicControl->favicon_driver, $basicControl->favicon, true) }}"
                     alt="{{ $basicControl->site_title }} Logo"
                     data-hs-theme-appearance="default">
                <img class="navbar-brand-logo-mini"
                     src="{{ getFile($basicControl->favicon_driver, $basicControl->favicon, true) }}"
                     alt="Logo"
                     data-hs-theme-appearance="dark">
            </a>
            <!-- End Logo -->

            <!-- Navbar Vertical Toggle -->
            <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i class="bi-arrow-bar-left navbar-toggler-short-align"
                   data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                   data-bs-toggle="tooltip"
                   data-bs-placement="right"
                   title="Collapse">
                </i>
                <i
                    class="bi-arrow-bar-right navbar-toggler-full-align"
                    data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                    data-bs-toggle="tooltip"
                    data-bs-placement="right"
                    title="Expand"
                ></i>
            </button>
            <!-- End Navbar Vertical Toggle -->


            <!-- Content -->
            <div class="navbar-vertical-content">
                <div id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">
                    @if(adminAccessRoute(config('role.dashboard.access.view')))
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.dashboard']) }}"
                               href="{{ route('admin.dashboard') }}">
                                <i class="bi-house-door nav-icon"></i>
                                <span class="nav-link-title">@lang("Dashboard")</span>
                            </a>
                        </div>
                    @endif

                    @if(adminAccessRoute(config('role.manage_staff.access.view')))
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.role.staff']) }}"
                               href="{{ route('admin.role.staff') }}">
                                <i class="fa-light fa-user nav-icon"></i>
                                <span class="nav-link-title">@lang("Manage Staff")</span>
                            </a>
                        </div>
                    @endif

                    @if(adminAccessRoute(config('role.manage_staff.access.view')))
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.role']) }}"
                               href="{{ route('admin.role') }}" data-placement="left">
                                <i class="fa-light fa-users-gear nav-icon"></i>
                                <span class="nav-link-title">@lang('Role & Permission')</span>
                            </a>
                        </div>
                    @endif

                    @if(adminAccessRoute(config('role.manage_game.access.view')))
                        <span class="dropdown-header mt-4">@lang('Manage Module')</span>
                        <small class="bi-three-dots nav-subtitle-replacer"></small>
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.listCategory']) }}"
                               href="{{ route('admin.listCategory') }}" data-placement="left">
                                <i class="bi bi-tags nav-icon"></i>
                                <span class="nav-link-title">@lang("Game Category")</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.listTournament']) }}"
                               href="{{ route('admin.listTournament') }}" data-placement="left">
                                <i class="fas fa-gamepad nav-icon "></i>
                                <span class="nav-link-title">@lang("Tournament")</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.listTeam']) }}"
                               href="{{ route('admin.listTeam') }}" data-placement="left">
                                <i class="fa fa-users nav-icon "></i>
                                <span class="nav-link-title">@lang("Team")</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.*Match*','admin.addQuestion']) }}"
                               href="{{ route('admin.listMatch') }}" data-placement="left">
                                <i class="bi bi-flag nav-icon "></i>
                                <span class="nav-link-title">@lang("Match")</span>
                            </a>
                        </div>

                            <div class="nav-item">
                                <a class="nav-link {{ menuActive(['admin.listSchedule']) }}"
                                   href="{{ route('admin.listSchedule') }}" data-placement="left">
                                    <i class="fa fa-calendar-alt nav-icon"></i>
                                    <span class="nav-link-title">@lang("Schedule")</span>
                                </a>
                            </div>
                    @endif

                    @if(adminAccessRoute(config('role.manage_result.access.view')))
                        <span class="dropdown-header mt-4">@lang('Manage Result')</span>
                        <small class="bi-three-dots nav-subtitle-replacer"></small>
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.resultList.pending']) }}"
                               href="{{ route('admin.resultList.pending') }}" data-placement="left">
                                <i class="fas fa-spinner nav-icon"></i>
                                <span class="nav-link-title">@lang("Pending Result")</span>
                            </a>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.resultList.complete']) }}"
                               href="{{ route('admin.resultList.complete') }}" data-placement="left">
                                <i class="fas fa-check nav-icon "></i>
                                <span class="nav-link-title">@lang("Close Result")</span>
                            </a>
                        </div>
                    @endif


                    @if(adminAccessRoute(config('role.support_ticket.access.view')))
                        <span class="dropdown-header mt-4"> @lang("Ticket Panel")</span>
                        <small class="bi-three-dots nav-subtitle-replacer"></small>
                        <div class="nav-item">
                            <a class="nav-link dropdown-toggle {{ menuActive(['admin.ticket', 'admin.ticket.search', 'admin.ticket.view'], 3) }}"
                               href="#navbarVerticalTicketMenu"
                               role="button"
                               data-bs-toggle="collapse"
                               data-bs-target="#navbarVerticalTicketMenu"
                               aria-expanded="false"
                               aria-controls="navbarVerticalTicketMenu">
                                <i class="fa-light fa-headset nav-icon"></i>
                                <span class="nav-link-title">@lang("Support Ticket")</span>
                            </a>
                            <div id="navbarVerticalTicketMenu"
                                 class="nav-collapse collapse {{ menuActive(['admin.ticket','admin.ticket.search', 'admin.ticket.view'], 2) }}"
                                 data-bs-parent="#navbarVerticalTicketMenu">
                                <a class="nav-link {{ request()->is('admin/tickets/all') ? 'active' : '' }}"
                                   href="{{ route('admin.ticket', 'all') }}">@lang("All Tickets")
                                </a>
                                <a class="nav-link {{ request()->is('admin/tickets/answered') ? 'active' : '' }}"
                                   href="{{ route('admin.ticket', 'answered') }}">@lang("Answered Ticket")</a>
                                <a class="nav-link {{ request()->is('admin/tickets/replied') ? 'active' : '' }}"
                                   href="{{ route('admin.ticket', 'replied') }}">@lang("Replied Ticket")</a>
                                <a class="nav-link {{ request()->is('admin/tickets/closed') ? 'active' : '' }}"
                                   href="{{ route('admin.ticket', 'closed') }}">@lang("Closed Ticket")</a>
                            </div>
                        </div>
                    @endif

                    @if(adminAccessRoute(config('role.user_management.access.view')) || adminAccessRoute(config('role.subscriber.access.view')))
                        <span class="dropdown-header mt-4"> @lang("User Panel")</span>
                        <small class="bi-three-dots nav-subtitle-replacer"></small>
                        @if(adminAccessRoute(config('role.user_management.access.view')))
                            <div class="nav-item">
                                <a class="nav-link dropdown-toggle {{ menuActive(['admin.users'], 3) }}"
                                   href="#navbarVerticalUserPanelMenu"
                                   role="button"
                                   data-bs-toggle="collapse"
                                   data-bs-target="#navbarVerticalUserPanelMenu"
                                   aria-expanded="false"
                                   aria-controls="navbarVerticalUserPanelMenu">
                                    <i class="bi-people nav-icon"></i>
                                    <span class="nav-link-title">@lang('User Management')</span>
                                </a>
                                <div id="navbarVerticalUserPanelMenu"
                                     class="nav-collapse collapse {{ menuActive(['admin.mail.all.user','admin.users','admin.users.add','admin.user.edit',
                                                                        'admin.user.view.profile','admin.user.transaction','admin.user.payment',
                                                                        'admin.user.payout','admin.user.kyc.list','admin.send.email'], 2) }}"
                                     data-bs-parent="#navbarVerticalUserPanelMenu">

                                    <a class="nav-link {{ menuActive(['admin.users']) }}"
                                       href="{{ route('admin.users') }}">
                                        @lang('All User')
                                    </a>

                                    <a class="nav-link {{ menuActive(['admin.mail.all.user']) }}"
                                       href="{{ route("admin.mail.all.user") }}">@lang('Mail To Users')</a>
                                </div>
                            </div>
                        @endif
                        @if(adminAccessRoute(config('role.subscriber.access.view')))
                            <div class="nav-item">
                                <a class="nav-link {{ menuActive(['admin.subscribe']) }}"
                                   href="{{ route('admin.subscribe') }}" data-placement="left">
                                    <i class="fas fa-users nav-icon"></i>
                                    <span class="nav-link-title">@lang('Subscribers')</span>
                                </a>
                            </div>
                        @endif
                    @endif

                    @if(adminAccessRoute(config('role.website_controls.access.view')))
                        <span class="dropdown-header mt-4"> @lang('SETTINGS PANEL')</span>
                        <small class="bi-three-dots nav-subtitle-replacer"></small>


                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(controlPanelRoutes()) }}"
                               href="{{ route('admin.settings') }}" data-placement="left">
                                <i class="bi bi-gear nav-icon"></i>
                                <span class="nav-link-title">@lang('Control Panel')</span>
                            </a>
                        </div>
                    @endif

                    @if(adminAccessRoute(config('role.theme_settings.access.view')))
                        <span class="dropdown-header mt-4">@lang("Themes Settings")</span>
                        <small class="bi-three-dots nav-subtitle-replacer"></small>
                        <div id="navbarVerticalThemeMenu">
                            <div class="nav-item">
                                <a class="nav-link {{ menuActive(['admin.page.index','admin.create.page','admin.edit.page']) }}"
                                   href="{{ route('admin.page.index', basicControl()->theme) }}"
                                   data-placement="left">
                                    <i class="fa-light fa-list nav-icon"></i>
                                    <span class="nav-link-title">@lang('Pages')</span>
                                </a>
                            </div>

                            <div class="nav-item">
                                <a class="nav-link {{ menuActive(['admin.manage.menu']) }}"
                                   href="{{ route('admin.manage.menu') }}" data-placement="left">
                                    <i class="bi-folder2-open nav-icon"></i>
                                    <span class="nav-link-title">@lang('Manage Menu')</span>
                                </a>
                            </div>
                        </div>

                        @php
                            $segments = request()->segments();
                            $last  = end($segments);
                        @endphp
                        <div class="nav-item">
                            <a class="nav-link dropdown-toggle {{ menuActive(['admin.manage.content', 'admin.manage.content.multiple', 'admin.content.item.edit*'], 3) }}"
                               href="#navbarVerticalContentsMenu"
                               role="button" data-bs-toggle="collapse"
                               data-bs-target="#navbarVerticalContentsMenu" aria-expanded="false"
                               aria-controls="navbarVerticalContentsMenu">
                                <i class="fa-light fa-pen nav-icon"></i>
                                <span class="nav-link-title">@lang('Manage Content')</span>
                            </a>
                            <div id="navbarVerticalContentsMenu"
                                 class="nav-collapse collapse {{ menuActive(['admin.manage.content', 'admin.manage.content.multiple', 'admin.content.item.edit*'], 2) }}"
                                 data-bs-parent="#navbarVerticalContentsMenu">
                                @foreach(array_diff(array_keys(config('contents')), ['message','content_media']) as $name)
                                    <a class="nav-link {{($last == $name) ? 'active' : '' }}"
                                       href="{{ route('admin.manage.content', $name) }}">@lang(stringToTitle($name))</a>
                                @endforeach
                            </div>
                        </div>

                        @foreach(collect(config('generalsettings.settings')) as $key => $setting)
                            <div class="nav-item d-none">
                                <a class="nav-link  {{ isMenuActive($setting['route']) }}"
                                   href="{{ getRoute($setting['route'], $setting['route_segment'] ?? null) }}">
                                    <i class="{{$setting['icon']}} nav-icon"></i>
                                    <span class="nav-link-title">{{ __(getTitle($key.' '.'Settings')) }}</span>
                                </a>
                            </div>
                        @endforeach
                    @endif

                        <span class="dropdown-header mt-4"> @lang('Application Panel')</span>
                        <small class="bi-three-dots nav-subtitle-replacer"></small>
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['clear']) }}"
                               href="{{ route('clear') }}" data-placement="left">
                                <i class="fas fa-sync nav-icon"></i>
                                <span class="nav-link-title">@lang('Cache Clear')</span>
                            </a>
                        </div>


                </div>

                <div class="navbar-vertical-footer">
                    <ul class="navbar-vertical-footer-list">
                        <li class="navbar-vertical-footer-list-item">
                            <span class="dropdown-header">@lang('Version 6.0')</span>
                        </li>
                        <li class="navbar-vertical-footer-list-item">
                            <div class="dropdown dropup">
                                <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle"
                                        id="selectThemeDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                        data-bs-dropdown-animation></button>
                                <div class="dropdown-menu navbar-dropdown-menu navbar-dropdown-menu-borderless"
                                     aria-labelledby="selectThemeDropdown">
                                    <a class="dropdown-item" href="javascript:void(0)" data-icon="bi-moon-stars"
                                       data-value="auto">
                                        <i class="bi-moon-stars me-2"></i>
                                        <span class="text-truncate"
                                              title="Auto (system default)">@lang("Default")</span>
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)" data-icon="bi-brightness-high"
                                       data-value="default">
                                        <i class="bi-brightness-high me-2"></i>
                                        <span class="text-truncate"
                                              title="Default (light mode)">@lang("Light Mode")</span>
                                    </a>
                                    <a class="dropdown-item active" href="javascript:void(0)" data-icon="bi-moon"
                                       data-value="dark">
                                        <i class="bi-moon me-2"></i>
                                        <span class="text-truncate" title="Dark">@lang("Dark Mode")</span>
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</aside>




