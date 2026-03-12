<?php

namespace ModularityArcgisMap\AcfField;

/**
 * Custom ACF field type: Open Street Map picker
 *
 * Renders an interactive Leaflet/OSM map in the ACF admin that lets the user
 * pick a coordinate and zoom level.  The stored value is a JSON string:
 *   {"lat": 57.7082, "lng": 11.9648, "zoom": 13}
 *
 * Filters
 * -------
 * Modularity/Module/ArcGISMap/DefaultLocation
 *   Passes the default map view (array with keys lat, lng, zoom).
 *   Use this to feed the settings-page values into the module-settings field.
 *
 * @param array $default  ['lat' => float, 'lng' => float, 'zoom' => int]
 * @return array
 */
class OpenStreetMap extends \acf_field
{
    public function initialize(): void
    {
        $this->name     = 'arcgis_open_street_map';
        $this->label    = __('ArcGIS Open Street Map', 'modularity-arcgis-map');
        $this->category = 'arcgis';
        $this->defaults = [
            'listen_default_location' => 1,
        ];
    }

    /**
     * Output the field HTML in the ACF admin.
     */
    public function render_field($field): void
    {
        $stored = $field['value'];

        // Decode stored JSON value if present
        if (is_string($stored) && $stored !== '') {
            $decoded = json_decode($stored, true);
            $stored  = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($stored)) {
            $stored = [];
        }

        $hasValue = isset($stored['lat']) && $stored['lat'] !== '';

        // Determine whether this field instance should listen to the default-location filter.
        $listenToFilter = isset($field['listen_default_location']) ? (bool) $field['listen_default_location'] : true;

        // Merge filter defaults with stored value only when listening is enabled.
        if ($listenToFilter) {
            $defaults = apply_filters('Modularity/Module/ArcGISMap/DefaultLocation', [
                'lat'  => 57.7082,
                'lng'  => 11.9648,
                'zoom' => 13,
            ]);
        } else {
            $defaults = [
                'lat'  => '',
                'lng'  => '',
                'zoom' => '',
            ];
        }

        $lat  = $hasValue ? (float) $stored['lat']  : ($defaults['lat'] !== '' ? (float) $defaults['lat'] : '');
        $lng  = $hasValue ? (float) $stored['lng']  : ($defaults['lng'] !== '' ? (float) $defaults['lng'] : '');
        $zoom = $hasValue ? (int)   $stored['zoom'] : ($defaults['zoom'] !== '' ? (int) $defaults['zoom'] : '');

        $fieldName = esc_attr($field['name']);
        $fieldId   = esc_attr($field['id']);
        ?>
        <div
            class="acf-osm-field"
            id="<?php echo $fieldId; ?>-wrapper"
            data-field-id="<?php echo $fieldId; ?>"
            data-lat="<?php echo esc_attr($lat); ?>"
            data-lng="<?php echo esc_attr($lng); ?>"
            data-zoom="<?php echo esc_attr($zoom); ?>"
            data-has-value="<?php echo $hasValue ? 'true' : 'false'; ?>"
        >
            <div class="acf-osm-field__map" id="<?php echo $fieldId; ?>-map"></div>

            <div class="acf-osm-field__coords">
                <label class="acf-osm-field__coord-label">
                    <span><?php _e('Latitude', 'modularity-arcgis-map'); ?></span>
                    <input
                        type="text"
                        class="acf-osm-field__lat"
                        value="<?php echo $hasValue ? esc_attr($lat) : ''; ?>"
                        placeholder="<?php echo esc_attr($defaults['lat']); ?>"
                        readonly
                    />
                </label>

                <label class="acf-osm-field__coord-label">
                    <span><?php _e('Longitude', 'modularity-arcgis-map'); ?></span>
                    <input
                        type="text"
                        class="acf-osm-field__lng"
                        value="<?php echo $hasValue ? esc_attr($lng) : ''; ?>"
                        placeholder="<?php echo esc_attr($defaults['lng']); ?>"
                        readonly
                    />
                </label>

                <label class="acf-osm-field__coord-label">
                    <span><?php _e('Zoom', 'modularity-arcgis-map'); ?></span>
                    <input
                        type="number"
                        class="acf-osm-field__zoom"
                        value="<?php echo $hasValue ? esc_attr($zoom) : ''; ?>"
                        placeholder="<?php echo esc_attr($defaults['zoom']); ?>"
                        min="1"
                        max="20"
                        readonly
                    />
                </label>

                <button type="button" class="button acf-osm-field__reset">
                    <?php _e('Reset to default', 'modularity-arcgis-map'); ?>
                </button>
            </div>

            <input
                type="hidden"
                name="<?php echo $fieldName; ?>"
                id="<?php echo $fieldId; ?>"
                value="<?php echo $hasValue ? esc_attr(json_encode(['lat' => $lat, 'lng' => $lng, 'zoom' => $zoom])) : ''; ?>"
            />
        </div>
        <?php
    }

    /**
     * No extra field-configuration settings needed.
     */
    public function render_field_settings($field): void
    {
        if (function_exists('acf_render_field_setting')) {
            acf_render_field_setting($field, [
                'label'        => __('Listen to default location', 'modularity-arcgis-map'),
                'instructions' => __('When enabled the field will use the DefaultLocation filter for its initial view. Disable on options/settings pages to avoid a pre-filled location.', 'modularity-arcgis-map'),
                'name'         => 'listen_default_location',
                'type'         => 'true_false',
                'ui'           => 1,
                'default_value'=> 1,
            ]);
        }
    }

    /**
     * Decode the stored JSON string into a typed array when reading the value.
     *
     * @param mixed  $value
     * @param mixed  $post_id
     * @param array  $field
     * @return array|null
     */
    public function format_value($value, $post_id, $field)
    {
        if (empty($value)) {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            return [
                'lat'  => isset($value['lat'])  ? (float) $value['lat']  : null,
                'lng'  => isset($value['lng'])  ? (float) $value['lng']  : null,
                'zoom' => isset($value['zoom']) ? (int)   $value['zoom'] : null,
            ];
        }

        return null;
    }
}
