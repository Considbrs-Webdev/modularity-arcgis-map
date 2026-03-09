@if (!$hideTitle && !empty($postTitle))
    @typography([
        'element' => 'h2',
        'variant' => 'h2',
        'classList' => ['arcgis-map-title', 'u-margin__top--0', 'u-margin__bottom--2']
    ])
        {{ $postTitle }}
    @endtypography
@endif

<figure class="arcgis-map-figure u-margin__y--0">
    {!! $mapHtml !!}

    @if (!empty($mapDescription))
        <figcaption class="arcgis-map-caption u-margin__top--1 u-font-size--meta">
            {!! $mapDescription !!}
        </figcaption>
    @endif
</figure>
