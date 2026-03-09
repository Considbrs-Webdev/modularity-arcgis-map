<?php

namespace ModularityArcgisMap;

use WPService\WpService;

use ModularityArcgisMap\Helper\MapRenderer;

class ArcgisMap extends \Modularity\Module
{
    public $slug = 'arcgis-map';
    public $icon = 'background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTEyIDJDOC4xMyAyIDUgNS4xMyA1IDljMCA1LjI1IDcgMTMgNyAxM3M3LTcuNzUgNy0xM2MwLTMuODctMy4xMy03LTctN3ptMCA5LjVjLTEuMzggMC0yLjUtMS4xMi0yLjUtMi41czEuMTItMi41IDIuNS0yLjUgMi41IDEuMTIgMi41IDIuNS0xLjEyIDIuNS0yLjUgMi41eiIvPjwvc3ZnPg==);';
    public $supports = array();
    public $isBlockCompatible = true;

    private WPService $wpService;

    public function init()
    {
        $this->nameSingular = __('ArcGIS Map', 'modularity-arcgis-map');
        $this->namePlural = __('ArcGIS Maps', 'modularity-arcgis-map');
        $this->description = __('Display ArcGIS maps', 'modularity-arcgis-map');
    }

    public function data(): array
    {
        $this->wpService = \Modularity\Helper\WpService::get();
        $fields = $this->getFields();

        $mapHtml = MapRenderer::render([
            'lat'          => $fields['latitude'],
            'lng'          => $fields['longitude'],
            'zoom'         => $fields['zoom_level'] ?? 14,
            'portalUrl'    => !empty($fields['portal_url']) ? $fields['portal_url'] : null,
            'webmapId'     => !empty($fields['map_id']) ? $fields['map_id'] : null,
            'markerUrl'    => !empty($fields['marker']) && $fields['marker'] !== false
                                ? $fields['marker']
                                : null,
            'markerWidth'  => !empty($fields['marker_width']) ? $fields['marker_width'] : 27,
            'markerHeight' => !empty($fields['marker_height']) ? $fields['marker_height'] : 40,
            'showMarker'   => $fields['show_marker'],
            'height'       => !empty($fields['height']) ? $fields['height'] . 'px' : '500px',
        ]);

        return [
            'mapHtml'        => $mapHtml,
            'mapDescription' => $fields['map_description'] ?? '',
        ];
    }

    /**
     * Blade Template
     * @return string
     */
    public function template(): string
    {
        return 'arcgis-map.blade.php';
    }

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization (if you must, use __construct with care, this will probably break stuff!!)
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}
