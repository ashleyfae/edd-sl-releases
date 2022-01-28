<?php
/**
 * Plugin Name: EDD Software Licensing Releases
 * Plugin URI: https://github.com/ashleyfae/edd-sl-releases
 * Description: Brings historical releases to EDD Software Licensing.
 * Version: 0.5
 * Author: Ashley Gibson
 * Author URI: https://github.com/ashleyfae
 * Text Domain: edd-sl-releases
 *
 * GitHub Plugin URI: ashleyfae/edd-sl-releases
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

use EddSlReleases\Plugin;

if (
    version_compare(phpversion(), '8.0', '<') ||
    version_compare(get_bloginfo('version'), '5.8', '<')
) {
    return;
}

const EDD_SL_RELEASES_VERSION = '0.5';
const EDD_SL_RELEASES_FILE    = __FILE__;

require 'vendor/autoload.php';

/**
 * Returns the requested object from the service container, or the main Plugin object.
 *
 * @since 1.0
 *
 * @param  string|null  $abstract
 *
 * @return Plugin|mixed
 */
function eddSlReleases($abstract = null)
{
    static $instance = null;

    if ($instance === null) {
        $instance = new Plugin();
    }

    if ($abstract !== null) {
        return $instance->make($abstract);
    }

    return $instance;
}

/**
 * When the plugin is installed, add an option to flag that the installer should run later.
 * Not doing it now because we don't know that this site has passed the necessary system
 * requirements, etc. So instead, we add an option to trigger it later after the plugin
 * is fully booted.
 */
register_activation_hook(EDD_SL_RELEASES_FILE, function () {
    if ( ! get_option( 'edd_sl_releases_version' ) ) {
        update_option( 'edd_sl_releases_run_install', time() );
    }

});

/**
 * Boots the plugin, if Software Licensing is installed.
 */
add_action('plugins_loaded', function () {
    if (function_exists('edd_software_licensing')) {
        eddSlReleases()->boot();
    }
}, 200);
