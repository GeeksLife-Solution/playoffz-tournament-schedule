<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link {{menuActive('user.profile')}}" aria-current="page" href="{{route('user.profile')}}">@lang('profile')</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{menuActive('user.updatePassword')}}" aria-current="page"
           href="{{route('user.updatePassword')}}">@lang('password')</a>
    </li>
    @isset($kycs)
        @foreach($kycs as $item)
            <li class="nav-item">
                <a class="nav-link {{request()->segment(count(request()->segments())) == $item->id ? 'active':''}}"
                   href="{{route('user.kyc',[$item->slug,$item->id])}}"> {{$item->name}}</a>
            </li>
        @endforeach
    @endisset
</ul>
