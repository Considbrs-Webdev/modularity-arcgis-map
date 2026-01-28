<?php

namespace ModularityArcgisMap;

use WPService\WpService;

class ArcgisMap extends \Modularity\Module
{
    public $slug = 'arcgis-map';
    public $icon = 'background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTEyIDJDOC4xMyAyIDUgNS4xMyA1IDljMCA1LjI1IDcgMTMgNyAxM3M3LTcuNzUgNy0xM2MwLTMuODctMy4xMy03LTctN3ptMCA5LjVjLTEuMzggMC0yLjUtMS4xMi0yLjUtMi41czEuMTItMi41IDIuNS0yLjUgMi41IDEuMTIgMi41IDIuNS0xLjEyIDIuNS0yLjUgMi41eiIvPjwvc3ZnPg==);';
    public $supports = array();
    public $isBlockCompatible = true;

    private WPService $wpService;

    public function init()
    {
        $this->nameSingular = __('ARCGIS Map', 'modularity-arcgis-map');
        $this->namePlural = __('ARCGIS Maps', 'modularity-arcgis-map');
        $this->description = __('Display ARCGIS maps', 'modularity-arcgis-map');
    }

    public function data(): array
    {
        $this->wpService = \Modularity\Helper\WpService::get();
        $fields = $this->getFields();

        $data = [
            'id'         => 'arcgis-map-' . uniqid(),
            'lat'        => $fields['lat'] ?? '65.319797',
            'lng'        => $fields['lng'] ?? '21.474190',
            'zoom'       => $fields['zoom'] ?? 14,
            'portalUrl'  => $fields['portal_url'] ?? 'https://pitea.maps.arcgis.com/',
            'webmapId'   => $fields['webmap_id'] ?? '0d275d0c94884258a24c70d3be3924b0',
            'markerUrl'  => $fields['marker_url'] ?? 'https://wip.pitea.se/karta/img/mappin_red.svg',
            'showMarker' => $fields['show_marker'] ?? true,
            'height'     => $fields['height'] ?? '500px',
        ];

        return $data;
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
