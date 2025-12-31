<?php
/**
 * PHPUnit Bootstrap
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since 1.0
 */

require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';

WP_Mock::setUsePatchwork( true);
WP_Mock::bootstrap();

add_filter('pre_http_request', function ($status = false, $args = [], $url = '') {
    return new \WP_Error('no_reqs_in_unit_tests', 'HTTP Requests disabled for unit tests.');
});
