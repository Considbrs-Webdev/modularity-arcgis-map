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
