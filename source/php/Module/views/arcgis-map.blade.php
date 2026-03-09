@if (!$hideTitle && !empty($postTitle))
    @typography([
        'element' => 'h2',
        'variant' => 'h2',
        'classList' => ['arcgis-map-title', 'u-margin__top--0', 'u-margin__bottom--2']
    ])
        {{ $postTitle }}
    @endtypography
@endif

{!! $mapHtml !!}

@if (!empty($mapDescription))
    @typography([
        'element' => 'div',
        'variant' => 'body',
        'classList' => ['arcgis-map-description', 'u-margin__top--2']
    ])
        {!! $mapDescription !!}
    @endtypography
@endif
