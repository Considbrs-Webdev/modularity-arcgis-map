<?php

namespace ModularityArcgisMap\Helper;

/**
 * Renders the ArcGIS map container HTML.
 *
 * Can be used from this plugin or any external plugin to generate a map
 * wrapper element that is picked up by the ArcGIS JS integration.
 *
 * @example
 *   echo \ModularityArcgisMap\Helper\MapRenderer::render([
 *       'lat'   => '65.3',
 *       'lng'   => '21.5',
 *       'zoom'  => 12,
 *       'height' => '400px',
 *   ]);
 */
class MapRenderer
{
    /**
     * Default configuration values.
     */
    private const DEFAULTS = [
        'id'           => null,
        'lat'          => '0',
        'lng'          => '0',
        'zoom'         => 14,
        'portalUrl'    => null,
        'webmapId'     => null,
        'markerUrl'    => null,
        'markerWidth'  => 27,
        'markerHeight' => 40,
        'showMarker'   => true,
        'height'       => '500px',
        'geoJsonData'  => null,
        'geoJsonTitle' => null,
    ];

    /**
     * Render the ArcGIS map container element.
     *
     * Unset or null values fall back to global plugin settings where applicable.
     *
     * @param array $config {
     *     Optional. Map configuration.
     *
     *     @type string      $id           HTML id attribute. Auto-generated when omitted.
     *     @type string      $lat          Latitude.
     *     @type string      $lng          Longitude.
     *     @type int         $zoom         Zoom level (default 14).
     *     @type string      $portalUrl    ArcGIS portal URL. Falls back to plugin setting.
     *     @type string      $webmapId     ArcGIS web-map ID. Falls back to plugin setting.
     *     @type string      $markerUrl    Marker image URL. Falls back to plugin setting.
     *     @type int         $markerWidth  Marker width in px (default 27).
     *     @type int         $markerHeight Marker height in px (default 40).
     *     @type bool        $showMarker   Whether to display the marker (default true).
     *     @type string      $height       CSS height of the container, e.g. '500px' (default '500px').
     *     @type array|null  $geoJsonData  GeoJSON FeatureCollection array to render as a layer (optional).
     *.    @type string|null $geoJsonTitle Optional title for the GeoJSON layer, shown in popups (default 'GeoJSON Layer').
     * }
     * @return string HTML string for the map container element.
     */
    public static function render(array $config = []): string
    {
        $config = array_merge(self::DEFAULTS, $config);

        if (empty($config['id'])) {
            $config['id'] = 'arcgis-map-' . uniqid();
        }

        if (empty($config['portalUrl'])) {
            $config['portalUrl'] = SettingsHelper::getPortalUrl();
        }

        if (empty($config['webmapId'])) {
            $config['webmapId'] = SettingsHelper::getMapId();
        }

        if (empty($config['markerUrl'])) {
            $config['markerUrl'] = SettingsHelper::getMarker();
        }

        $showMarker  = !empty($config['showMarker']) ? 'true' : 'false';
        $height      = !empty($config['height']) ? $config['height'] : '500px';
        $geoJsonAttr = '';

        if (!empty($config['geoJsonData'])) {
            $json        = is_array($config['geoJsonData'])
                ? wp_json_encode($config['geoJsonData'], JSON_UNESCAPED_UNICODE)
                : $config['geoJsonData'];
            $geoJsonAttr = ' data-geojson="' . esc_attr($json) . '"';
        }

        if (!empty($config['geoJsonTitle'])) {
            $geoJsonAttr .= ' data-geojson-title="' . esc_attr($config['geoJsonTitle']) . '"';
        }

        return sprintf(
            '<div class="modularity-arcgis-map"%s data-lat="%s" data-lng="%s" data-zoom="%s" data-portal-url="%s" data-webmap-id="%s" data-marker-url="%s" data-show-marker="%s" data-marker-width="%s" data-marker-height="%s"%s style="width: 100%%; height: %s;"></div>',
            ' id="' . esc_attr($config['id']) . '"',
            esc_attr($config['lat']),
            esc_attr($config['lng']),
            esc_attr($config['zoom']),
            esc_attr($config['portalUrl']),
            esc_attr($config['webmapId']),
            esc_attr($config['markerUrl']),
            esc_attr($showMarker),
            esc_attr($config['markerWidth']),
            esc_attr($config['markerHeight']),
            $geoJsonAttr,
            esc_attr($height)
        );
    }
}
