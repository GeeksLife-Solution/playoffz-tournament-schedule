<!-- blog details -->
@if (isset($blog['single']))
    <section class="blog-details blog-list">
        <div class="container">
            <div class="row gy-5 g-lg-5 d-flex justify-content-center">
                <div class="col-lg-10">
                    @if(isset($blog['multiple']) && !empty($blog['multiple']))
                        @foreach($blog['multiple'] as $blog)
                            <div class="blog-box row">
                                <div class="col-md-6 img-box">
                                    <img
                                        src="{{getFile($blog['media']->image->driver,$blog['media']->image->path)}}"
                                        class="img-fluid"
                                        alt="..."
                                    />
                                </div>
                                <div class="col-md-6 text-box">
                                    <a href="{{route('blogDetails',[$blog['id'],slug($blog['title'])])}}" class="title">
                                        {{$blog['title']}}
                                    </a>
                                    <div class="date-author">
                           <span class="author">
                              <i class="fas fa-dot-circle"></i>@lang('Admin')
                           </span>
                                        <span class="float-end"></span>
                                    </div>
                                    {!! $blog['description'] !!}
                                    <a href="{{route('blogDetails',[$blog['id'],slug($blog['title'])])}}"
                                       class="read-more">@lang('Read more')</a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
