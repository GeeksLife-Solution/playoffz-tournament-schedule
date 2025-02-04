<!-- slider -->
@php
    $sliders = \App\Models\ContentDetails::with('content')
            ->whereHas('content', function ($query) {
                $query->where('name', 'slider');
            })
            ->get();
@endphp
@if(isset($sliders))
    <div class="skitter-large-box">
        <div class="skitter skitter-large with-dots">
            <ul>
                @foreach($sliders as $data)
                    <li>
                        <a href="{{@$data->content->media->my_link}}">
                            <img
                                src="{{getFile(@$data->content->media->image->driver,@$data->content->media->image->path)}}"
                                class="downBars"
                            />
                        </a>
                        <div class="label_text">
                            <h2>{{@$data->description->name}}</h2>
                            <p class="mb-4">
                                {{@$data->description->short_description}}
                            </p>
                            <a href="{{@$data->content->media->my_link}}">
                                <button class="btn-custom"> {{@$data->description->button_name}}</button>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
