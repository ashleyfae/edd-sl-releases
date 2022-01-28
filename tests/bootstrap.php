<?php
/**
 * PHPUnit Bootstrap
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since 1.0
 */

$testsDir = getenv('WP_TESTS_DIR') ? : '/tmp/wordpress-tests-lib';
require_once $testsDir.'/includes/functions.php';

require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';

/**
 * Load plugin files.
 */
tests_add_filter('muplugins_loaded', function () {
    // Load EDD
    if (file_exists('/tmp/wordpress/wp-content/plugins/easy-digital-downloads/easy-digital-downloads.php')) {
        require '/tmp/wordpress/wp-content/plugins/easy-digital-downloads/easy-digital-downloads.php';
    } else {
        require dirname(__FILE__).'/../../easy-digital-downloads/easy-digital-downloads.php';
    }

    // Load SL
    $files = [
        '/tmp/wordpress/wp-content/plugins/EDD-Software-Licensing/edd-software-licenses.php',
        dirname(__FILE__).'/../../EDD-Software-Licensing/edd-software-licenses.php',
        dirname(__FILE__).'/../../edd-software-licensing/edd-software-licenses.php',
    ];

    foreach($files as $file) {
        if (file_exists($file)) {
            require $file;
            break;
        }
    }

    require dirname(__FILE__).'/../edd-sl-releases.php';
});

tests_add_filter('wp_is_application_passwords_available', '__return_true');

// Start up the WP testing environment.
require $testsDir.'/includes/bootstrap.php';

echo "Installing Easy Digital Downloads...\n";
activate_plugin('easy-digital-downloads/easy-digital-downloads.php');
edd_run_install();

echo "Installing Software Licensing...\n";
activate_plugin('EDD-Software-Licensing/edd-software-licenses.php');
edd_sl_install();

echo "Installing EDD SL Releases...\n";
activate_plugin('edd-sl-releases/edd-sl-releases.php');
eddSlReleases()->boot();
if (eddSlReleases(\EddSlReleases\Database\ReleasesTable::class)->exists()) {
    eddSlReleases(\EddSlReleases\Database\ReleasesTable::class)->uninstall();
}
eddSlReleases(\EddSlReleases\Database\ReleasesTable::class)->createOrUpdateTable();

add_filter('pre_http_request', function ($status = false, $args = [], $url = '') {
    return new \WP_Error('no_reqs_in_unit_tests', 'HTTP Requests disabled for unit tests.');
});
