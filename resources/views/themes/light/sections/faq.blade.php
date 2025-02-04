<!-- faq section -->
@if (isset($faq['single']))
    <section class="faq-section faq-page">
        <div class="container">
            <div class="row g-4 gy-5 justify-content-center align-items-center">
                <div class="col-lg-12">
                    <div class="accordion" id="accordionExample">
                        @if(isset($faq['multiple']) && !empty($faq['multiple']))
                            @foreach($faq['multiple'] as $key => $faq)
                                <div class="accordion-item">
                                    <h5 class="accordion-header" id="heading{{$key}}">
                                        <button
                                            class="accordion-button {{$key == 0 ? '':'collapsed'}}"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{$key}}"
                                            aria-expanded="true"
                                            aria-controls="collapse{{$key}}"
                                        >
                                            {{$faq['title']}}
                                        </button>
                                    </h5>
                                    <div
                                        id="collapse{{$key}}"
                                        class="accordion-collapse collapse {{$key == 0 ? 'show':''}}"
                                        aria-labelledby="heading{{$key}}"
                                        data-bs-parent="#accordionExample"
                                    >
                                        <div class="accordion-body">
                                            {!! $faq['description'] !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
