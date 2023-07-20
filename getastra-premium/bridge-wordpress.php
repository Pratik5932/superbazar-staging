<?php

namespace AstraPrefixed;

/*
 Plugin Name: Astra Security (Premium)
 Plugin URI: https://www.getastra.com
 description: Solid Firewall, Malware Removal and Vulnerability Scanning.
 Version: 2.1.47
 Author: Astra Security
 Requires PHP: 7.1
 Author URI: https://www.getastra.com/?utm_source=wp-plugins&utm_campaign=author-uri&utm_medium=wp-dash
*/
\defined('ABSPATH') or 'Plugin file cannot be accessed directly.';
\define('GK_DIR_PATH', __DIR__ . \DIRECTORY_SEPARATOR);
\define('GK_WP_BRIDGE_PATH', \GK_DIR_PATH . 'bridge' . \DIRECTORY_SEPARATOR . 'wordpress' . \DIRECTORY_SEPARATOR);
$astraDependencyErrors = (require_once \GK_DIR_PATH . '/dependencyChecker.php');
if (isset($astraDependencyErrors['errors']) && \count($astraDependencyErrors['errors']) > 0) {
    add_action('admin_notices', function () use($astraDependencyErrors) {
        $errorsPretty = \implode(". ", \array_values($astraDependencyErrors['errors']));
        echo '<div class="error"><p>⛔ Astra  is not enabled  due to the following errors: ' . $errorsPretty . '</p></div>';
    });
    return;
}
if (!isset($astraApp)) {
    // Load the GK engine
    require_once \GK_DIR_PATH . '/autoload.php';
}
add_action('init', function () use($astraContainer, $astraApp) {
    // Login hooks
    try {
        require_once \GK_WP_BRIDGE_PATH . 'includes/AstraDependencyCheck.php';
        require_once \GK_WP_BRIDGE_PATH . 'includes/AstraLoginHook.php';
        require_once \GK_WP_BRIDGE_PATH . 'includes/AstraAdminPage.php';
    } catch (\Exception $e) {
        if (\defined('ASTRA_DEBUG_MODE') && \true == \ASTRA_DEBUG_MODE) {
            throw $e;
        }
    }
});
