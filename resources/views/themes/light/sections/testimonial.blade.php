<!-- testimonial section -->
@if (isset($testimonial['single']))
    <section class="testimonial-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="header-text mb-5 text-center">
                        <h5>{{$testimonial['single']['title']}}</h5>
                        <h3>{{$testimonial['single']['sub_title']}}</h3>
                        {!! $testimonial['single']['short_description'] !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="testimonials owl-carousel">
                        @if(isset($testimonial['multiple']) && !empty($testimonial['multiple']))
                            @foreach($testimonial['multiple'] as $item)
                                <div class="review-box">
                                    <div class="upper">
                                        <div class="img-box">
                                            <img
                                                src="{{getFile($item['media']->image->driver,$item['media']->image->path)}}"
                                                alt="..."/>
                                        </div>
                                        <div class="client-info">
                                            <h5>{{$item['name']}}</h5>
                                            <span>{{$item['designation']}}</span>
                                        </div>
                                    </div>
                                    {!! $item['description'] !!}
                                    <i class="fad fa-quote-right quote"></i>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
