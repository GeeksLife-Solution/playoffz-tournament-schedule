@if(in_array(Request::route()->getName(),['home','category','tournament','match']))
    @php
        $pageDetails = \App\Models\PageDetail::with('page')
                ->whereHas('page', function ($query) {
                    $query->where(['slug' => '/', 'template_name' => basicControl()->theme]);
                })
                ->firstOrFail();

        $pageSeo = [
           'page_title' => optional($pageDetails->page)->page_title,
           'meta_title' => optional($pageDetails->page)->meta_title,
           'meta_keywords' => implode(',', optional($pageDetails->page)->meta_keywords ?? []),
           'meta_description' => optional($pageDetails->page)->meta_description,
           'og_description' => optional($pageDetails->page)->og_description,
           'meta_robots' => optional($pageDetails->page)->meta_robots,
           'meta_image' => getFile(optional($pageDetails->page)->meta_image_driver, optional($pageDetails->page)->meta_image),
       ];

    @endphp
@endif


<meta content="{{ isset($pageSeo['meta_description']) ? $pageSeo['meta_description'] : '' }}" name="description">
<meta
    content="{{ is_array(@$pageSeo['meta_keywords']) ? implode(', ', @$pageSeo['meta_keywords']) : @$pageSeo['meta_keywords'] }}"
    name="keywords">
<meta name="theme-color" content="{{ basicControl()->primary_color }}">
<meta name="author" content="{{basicControl()->site_title}}">
<meta name="robots" content="{{ isset($pageSeo['meta_robots']) ? $pageSeo['meta_robots'] : '' }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ isset(basicControl()->site_title) ? basicControl()->site_title : '' }}">
<meta property="og:title" content="{{ isset($pageSeo['meta_title']) ? $pageSeo['meta_title'] : '' }}">
<meta property="og:description" content="{{ isset($pageSeo['og_description']) ? $pageSeo['og_description'] : '' }}">
<meta property="og:image" content="{{  @$pageSeo['meta_image']}}">

<meta name="twitter:card" content="{{ isset($pageSeo['meta_title']) ? $pageSeo['meta_title'] : '' }}">
<meta name="twitter:title" content="{{ isset($pageSeo['meta_title']) ? $pageSeo['meta_title'] : '' }}">
<meta name="twitter:description"
      content="{{ isset($pageSeo['meta_description']) ? $pageSeo['meta_description'] : '' }}">
<meta name="twitter:image" content="{{  @$pageSeo['meta_image'] }}">
