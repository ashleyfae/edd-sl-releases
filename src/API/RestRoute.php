<?php
/**
 * RestRoute.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\API;

interface RestRoute
{

    const NAMESPACE = 'af-edd-sl-releases';

    /**
     * Register the route with WordPress using the `register_rest_route` function.
     *
     * @see register_rest_route()
     *
     * @since 1.0
     *
     * @return void
     */
    public function register(): void;

}
