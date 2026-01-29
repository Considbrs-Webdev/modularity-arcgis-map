<?php

namespace ModularityArcgisMap;

use ModularityArcgisMap\Helper\CacheBust;
use ModularityArcgisMap\Helper\SettingsHelper;

class App
{
    public function __construct()
    {
        // Register module
        add_action('init', array($this, 'registerModule'));

        // Register ACF options page
        add_action('acf/init', array($this, 'registerOptionsPage'));

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
