<?php

/**
 * Plugin Name:       Modularity ARCGIS Map
 * Plugin URI:        https://github.com/considbrs-webdev/modularity-arcgis-map.git
 * Description:       A Modularity module that implements ARCGIS maps according to their JS API.
 * Version: 1.0.0
 * Author:            Consid Borås AB
 * Author URI:        https://github.com/considbrs-webdev
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-arcgis-map
 * Domain Path:       /languages
 */

// Protect agains direct file access
if (!defined('WPINC')) {
    die;
}

define('MODULARITY_ARCGIS_MAP_PATH', plugin_dir_path(__FILE__));
define('MODULARITY_ARCGIS_MAP_URL', plugins_url('', __FILE__));
define('MODULARITY_ARCGIS_MAP_VIEW_PATH', MODULARITY_ARCGIS_MAP_PATH . 'views/');
define('MODULARITY_ARCGIS_MAP_MODULE_VIEW_PATH', plugin_dir_path(__FILE__) . 'source/php/Module/views');
define('MODULARITY_ARCGIS_MAP_MODULE_PATH', MODULARITY_ARCGIS_MAP_PATH . 'source/php/Module/');
    
add_action('init', function() {
    load_plugin_textdomain('modularity-arcgis-map', false, plugin_basename(dirname(__FILE__)) . '/languages');
}); 

// Autoload from plugin
if (file_exists(MODULARITY_ARCGIS_MAP_PATH . 'vendor/autoload.php')) {
    require_once MODULARITY_ARCGIS_MAP_PATH . 'vendor/autoload.php';
}
require_once MODULARITY_ARCGIS_MAP_PATH . 'Public.php';

// Acf auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-arcgis-map');
    $acfExportManager->setExportFolder(MODULARITY_ARCGIS_MAP_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'settings' => 'group_697b7f6b93f76',
        'module-settings' => 'group_697b846d25056',
    ));
    $acfExportManager->import();
}); 

// Modularity 3.0 ready - ViewPath for Component library
add_filter('/Modularity/externalViewPath', function ($arr) {
    $arr['mod-arcgis-map'] = MODULARITY_ARCGIS_MAP_MODULE_VIEW_PATH;
    return $arr;
}, 10, 3);

// Start application
new ModularityArcgisMap\App();
