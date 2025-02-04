@extends($theme . 'layouts.app')
@section('title',trans('Blog Details'))
@section('content')
    <section class="blog-details blog-list">
        <div class="container">
            <div class="row gy-5 g-lg-5">
                <div class="col-lg-8">
                    <div class="blog-box row">
                        <div class="col-md-12 img-box">
                            <img
                                src="{{getFile(@$blogDetails->content->media->image->driver,@$blogDetails->content->media->image->path)}}"
                                class="img-fluid"
                                alt="..."
                            />
                        </div>
                        <div class="col-md-12 text-box">
                            <a href="javascript:void(0)" class="title">
                                {{$blogDetails->description->title}}
                            </a>
                            <div class="date-author">
                           <span class="author">
                              <i class="fas fa-dot-circle"></i>@lang('Admin')
                           </span>
                                <span class="float-end">{{dateTime($blogDetails->created_at)}}</span>
                            </div>
                            {{$blogDetails->description->description}}
                        </div>
                    </div>
                </div>
                @if(!empty($relatedPosts))
                    <div class="col-lg-4">
                        <h4>@lang('Related Posts')</h4>
                        @foreach($relatedPosts as $post)
                            <div class="related-post">
                                <div class="img-box">
                                    <img
                                        class="img-fluid"
                                        src="{{getFile($post->media->image->driver,$post->media->image->path)}}"
                                        alt="..."
                                    />
                                </div>
                                <div class="text-box">
                                    <a href="{{route('blogDetails',[@$post->contentDetails[0]->id,slug(@$post->contentDetails[0]->description->title)])}}"
                                       class="title">
                                        {{@$post->contentDetails[0]->description->title}}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
