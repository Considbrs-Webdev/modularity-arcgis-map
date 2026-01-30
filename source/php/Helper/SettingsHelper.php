<?php

namespace ModularityArcgisMap\Helper;

class SettingsHelper
{
    /**
     * Default values for settings
     */
    private const DEFAULTS = [
        'sdk_url' => 'https://js.arcgis.com/4.33/',
        'theme_url' => 'https://js.arcgis.com/4.33/esri/themes/light/main.css',
        'portal_url' => false,
        'map_id' => false,
    ];

    /**
     * Options page identifier
     */
    private const OPTIONS_PAGE = 'modularity-arcgis-map-settings';

    /**
     * Get SDK URL
     *
     * @return string
     */
    public static function getSdkUrl(): string
    {
        return self::getOption('sdk_url');
    }

    /**
     * Get Theme URL
     *
     * @return string
     */
    public static function getThemeUrl(): string
    {
        return self::getOption('theme_url');
    }

    /**
     * Get Portal URL
     *
     * @return string
     */
    public static function getPortalUrl(): string
    {
        return self::getOption('portal_url');
    }

    /**
     * Get Map ID
     *
     * @return string
     */
    public static function getMapId(): string
    {
        return self::getOption('map_id');
    }

    /**
     * Get Marker URL
     *
     * @return string
     */
    public static function getMarker(): string
    {
        $value = get_field('marker', self::OPTIONS_PAGE);
        
        if (empty($value)) {
            // Return URL to default marker.svg in plugin assets
            return plugins_url('assets/images/marker.svg', dirname(dirname(dirname(__FILE__))));
        }
        
        return (string) $value;
    }

    /**
     * Get option value with fallback to default
     *
     * @param string $key
     * @return string
     */
    private static function getOption(string $key): string
    {
        $value = get_field($key, self::OPTIONS_PAGE);
        
        if (empty($value) && isset(self::DEFAULTS[$key])) {
            return self::DEFAULTS[$key];
        }
        
        return (string) $value;
    }

    /**
     * Get all settings as an array
     *
     * @return array
     */
    public static function getAllSettings(): array
    {
        return [
            'sdk_url' => self::getSdkUrl(),
            'theme_url' => self::getThemeUrl(),
            'portal_url' => self::getPortalUrl(),
            'map_id' => self::getMapId(),
            'marker' => self::getMarker(),
        ];
    }
}
