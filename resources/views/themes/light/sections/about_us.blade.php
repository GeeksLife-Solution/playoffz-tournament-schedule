<!-- about section -->
@if (isset($about_us['single']))
    <section class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="img-box">
                        <img
                            src="{{getFile(@$about_us['single']['media']->image->driver,@$about_us['single']['media']->image->path)}}"
                            alt="" class="img-fluid"/>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="header-text">
                        <h5>@lang($about_us['single']['title'])</h5>
                        <h2>@lang($about_us['single']['sub_title'])</h2>
                    </div>
                    {!! $about_us['single']['description'] !!}
                </div>
            </div>
        </div>
    </section>
@endif
