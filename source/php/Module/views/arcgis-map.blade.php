@if (!$hideTitle && !empty($postTitle))
    @typography([
        'element' => 'h2',
        'variant' => 'h2',
        'classList' => ['arcgis-map-title', 'u-margin__top--0', 'u-margin__bottom--2']
    ])
        {{ $postTitle }}
    @endtypography
@endif

<div class="modularity-arcgis-map" id="{{ $id }}" data-lat="{{ $lat }}" data-lng="{{ $lng }}"
    data-zoom="{{ $zoom }}" data-portal-url="{{ $portalUrl }}" data-webmap-id="{{ $webmapId }}"
    data-marker-url="{{ $markerUrl }}" data-show-marker="{{ $showMarker ? 'true' : 'false' }}"
    style="width: 100%; height: {{ $height }};"></div>
