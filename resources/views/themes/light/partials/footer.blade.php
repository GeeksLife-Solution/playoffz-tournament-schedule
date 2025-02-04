<!-- FOOTER SECTION -->
@if(!in_array(Request::route()->getName(),['home','category','tournament','match','login','register','register.sponsor','user.check','password.request']))
    <footer class="footer-section">
        <div class="overlay">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="footer-box">
                            <a class="navbar-brand" href="{{url('/')}}"> @lang(basicControl()->site_title) </a>
                            <p>
                                {{@$footer->description->message}}
                            </p>
                            <ul>
                                <li>
                                    <i class="far fa-phone-alt"></i>
                                    <span>{{@$footer->description->phone}}</span>
                                </li>
                                <li>
                                    <i class="far fa-envelope"></i>
                                    <span>{{@$footer->description->email}}</span>
                                </li>
                                <li>
                                    <i class="far fa-map-marker-alt"></i>
                                    <span>{{@$footer->description->address}}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 ps-lg-5">
                        <div class="footer-box">
                            <h5>@lang('Quick Links')</h5>
                            <ul>
                                @if(getFooterMenuData('useful_link') != null)
                                    @foreach(getFooterMenuData('useful_link') as $list)
                                        {!! $list !!}
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 ps-lg-5">
                        <div class="footer-box">
                            <h5>@lang('OUR Services')</h5>
                            <ul>
                                @if(getFooterMenuData('support_link') != null)
                                    @foreach(getFooterMenuData('support_link') as $list)
                                        {!! $list !!}
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="footer-box">
                            <h5>@lang('subscribe newsletter')</h5>
                            <form action="{{ route('subscribe') }}" method="post">
                                @csrf
                                <div class="input-group mb-3">
                                    <input
                                        type="email"
                                        name="email" value="{{old('email')}}"
                                        class="form-control"
                                        placeholder="@lang('Your email')"
                                        aria-label="Subscribe Newsletter"
                                        aria-describedby="basic-addon"
                                        required/>
                                    <button type="submit">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                    @error('email')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            </form>
                            @if(isset($extraInfo['social']) && count($extraInfo['social']) > 0)
                                <div class="social-links">
                                    @foreach($extraInfo['social'] as $social)
                                        <a href="{{@$social->content->media->my_link}}"><i
                                                class="{{@$social->content->media->icon}}"></i></a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="copyright">
                                @lang('Copyright') © {{date('Y')}} <a
                                    href="{{url('/')}}">@lang(basicControl()->site_title)</a> @lang('All Rights Reserved')
                            </p>
                        </div>
                        @if (isset($languages))
                            <div class="col-md-6 language">
                                @foreach ($languages as $item)
                                    <a href="{{route('language',$item->short_name)}}">{{$item->name}}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </footer>
@endif
