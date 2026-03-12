<?php

namespace ModularityArcgisMap;

use ModularityArcgisMap\Helper\CacheBust;
use ModularityArcgisMap\Helper\SettingsHelper;
use ModularityArcgisMap\AcfField\OpenStreetMap;

class App
{
    public function __construct()
    {
        // Register module
        add_action('init', array($this, 'registerModule'));

        // Register ACF options page and custom field type
        add_action('acf/init', array($this, 'registerOptionsPage'));
        add_action('acf/init', array($this, 'registerAcfFieldTypes'));

        // Register & enqueue ACF admin assets (scripts/styles for custom field)
        add_action('admin_enqueue_scripts', array($this, 'registerAdminAssets'));

        // Feed the settings-page start location into the module-settings OSM field
        add_filter('Modularity/Module/ArcGISMap/DefaultLocation', array($this, 'defaultLocationFromSettings'));

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    /**
     * Enqueue styles
     * @return void
     */
    public function enqueueStyles()
    {
        $styleFile = CacheBust::name('css/modularity-arcgis-map.css');

        if ($styleFile) {
            wp_enqueue_style(
                'modularity-arcgis-map',
                MODULARITY_ARCGIS_MAP_URL . '/assets/dist/' . $styleFile,
                array(),
                null
            );
        }
    }

    /**
     * Enqueue scripts
     * @return void
     */
    public function enqueueScripts()
    {
        $scriptFile = CacheBust::name('js/modularity-arcgis-map.js');

        if ($scriptFile) {
            wp_enqueue_script(
                'modularity-arcgis-map',
                MODULARITY_ARCGIS_MAP_URL . '/assets/dist/' . $scriptFile,
                array(),
                null,
                true
            );

            // Localize settings for the frontend script
            $settings = SettingsHelper::getAllSettings();
            wp_localize_script('modularity-arcgis-map', 'ModularityArcgisMapSettings', $settings);
        }
    }

    /**
     * Register ACF custom field types
     * @return void
     */
    public function registerAcfFieldTypes()
    {
        if (function_exists('acf_register_field_type')) {
            acf_register_field_type(new OpenStreetMap());
        }
    }

    /**
     * Register admin-only scripts and styles for the custom ACF field.
     * Directly enqueue the ACF field assets on every admin page.
     * This avoids timing issues with ACF's enqueue() callback and ensures
     * both the SCSS output and the Vite-extracted Leaflet CSS are loaded.
     * @return void
     */
    public function registerAdminAssets()
    {
        // Our compiled SCSS (field layout, map height, etc.)
        $cssFile = CacheBust::name('css/acf-field-osm.css');
        // Vite extracts the Leaflet CSS (imported inside acf-field-osm.js)
        // into a sibling file; its manifest key matches the JS entry name.
        $leafletCssFile = CacheBust::name('acf-field-osm.css');
        $jsFile = CacheBust::name('js/acf-field-osm.js');

        if ($leafletCssFile) {
            wp_enqueue_style(
                'acf-field-osm-leaflet',
                MODULARITY_ARCGIS_MAP_URL . '/assets/dist/' . $leafletCssFile,
                [],
                null
            );
        }

        if ($cssFile) {
            wp_enqueue_style(
                'acf-field-osm',
                MODULARITY_ARCGIS_MAP_URL . '/assets/dist/' . $cssFile,
                [],
                null
            );
        }

        if ($jsFile) {
            wp_enqueue_script(
                'acf-field-osm',
                MODULARITY_ARCGIS_MAP_URL . '/assets/dist/' . $jsFile,
                [],
                null,
                true
            );
        }
    }

    /**
     * Provide the settings-page start location as the default for the OSM field.
     *
     * @param  array $default  ['lat' => float, 'lng' => float, 'zoom' => int]
     * @return array
     */
    public function defaultLocationFromSettings(array $default): array
    {
        $stored = get_field('start_position', 'modularity-arcgis-map-settings');

        if (is_array($stored) && isset($stored['lat'], $stored['lng'], $stored['zoom'])) {
            return [
                'lat'  => (float) $stored['lat'],
                'lng'  => (float) $stored['lng'],
                'zoom' => (int)   $stored['zoom'],
            ];
        }

        return $default;
    }

    /**
     * Register the module
     * @return void
     */
    public function registerModule()
    {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITY_ARCGIS_MAP_MODULE_PATH,
                'ArcgisMap'
            );
        }
    }

    /**
     * Register ACF options page
     * @return void
     */
    public function registerOptionsPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page(array(
                'page_title'    => __('ArcGIS Map Settings', 'modularity-arcgis-map'),
                'menu_title'    => __('ArcGIS Map Settings', 'modularity-arcgis-map'),
                'menu_slug'     => 'modularity-arcgis-map-settings',
                'post_id'       => 'modularity-arcgis-map-settings',
                'capability'    => 'manage_options',
                'parent_slug'   => 'options-general.php',
                'post_id'       => 'modularity-arcgis-map-settings',
                'position'      => false,
                'icon_url'      => false,
            ));
        }
    }
}
