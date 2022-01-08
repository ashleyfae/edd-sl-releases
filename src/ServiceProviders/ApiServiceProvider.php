<?php
/**
 * ApiServiceProvider.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\ServiceProviders;

use EddSlReleases\API\v1;

class ApiServiceProvider implements ServiceProvider
{
    private array $routes = [
        v1\CreateRelease::class,
        v1\ListReleases::class,
    ];

    public function register(): void
    {

    }

    public function boot(): void
    {
        add_action('rest_api_init', function () {
            foreach ($this->routes as $route) {
               eddSlReleases()->make($route)->register();
            }
        });
    }
}
